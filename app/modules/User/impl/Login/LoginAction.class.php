<?php

use Honeybee\Core\Security\Auth;
use Honeybee\Core\Config;

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_LoginAction extends UserBaseAction
{
    /**
     * Execute our read logic, hence get the login prompt up.
     *
     * @param AgaviParameterHolder $request_data
     *
     * @return string The name of the view to execute.
     */
    public function executeRead(AgaviParameterHolder $request_data)
    {
        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', false));

        // forward to write if someone is passing our action the required parameters for login (e.g. basic auth)
        if ($request_data->hasParameter('username') && $request_data->hasParameter('password')) {
            return $this->authenticate($request_data);
        }

        return 'Input';
    }

    /**
     * Try to login based on the account information, that is provided with our given $rd.
     *
     * @param AgaviParameterHolder $request_data
     *
     * @return string The name of the view to execute.
     */
    public function executeWrite(AgaviParameterHolder $request_data)
    {
        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', false));

        return $this->authenticate($request_data);
    }

    /**
     * This method handles validation errors that occur upon our received input data.
     *
     * @param AgaviRequestDataHolder $request_data
     *
     * @return string The name of the view to execute.
     */
    public function handleError(AgaviRequestDataHolder $request_data)
    {
        $validation_manager = $this->getContainer()->getValidationManager();

        $this->logError(
            "[UNAUTHORIZED] Failed authentication attempt for username '",
            $request_data->getParameter('username'),
            "' - validation failed:",
            $validation_manager
        );

        $errors = array();
        foreach ($validation_manager->getErrors() as $field => $error) {
            $errors[$field] = $error['messages'][0];
        }

        $errors['auth'] = $this->getContext()->getTranslationManager()->_('invalid_login', 'user.errors');

        $this->setAttribute('errors', $errors);
        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', FALSE));

        return 'Error';
    }

    /**
     * Tries to authenticate the user with the given request data.
     *
     * @param \AgaviRequestDataHolder $request_data
     *
     * @return string name of agavi view to select
     */
    protected function authenticate($request_data) {

        $translation_manager = $this->getContext()->getTranslationManager();
        $user = $this->getContext()->getUser();

        $username = $request_data->getParameter('username');
        $password = $request_data->getParameter('password');

        $auth_provider = $this->getAuthProvider();
        $auth_response = $auth_provider->authenticate($username, $password);

        $log_message_part = sprintf("for username '$username' via auth provider %s.", get_class($auth_provider));

        if ($auth_response->getState() === Auth\AuthResponse::STATE_AUTHORIZED) {
            if (session_regenerate_id(true)) {
                $view_name = 'Success';

                $user->setAuthenticated(true);
                $user->setAttributes($this->getUserAttributes($auth_provider, $auth_response));

                $this->logInfo("[AUTHORIZED] Successful authentication attempt " . $log_message_part);
            } else {
                $view_name = 'Error';

                $error_message = $translation_manager->_('session_regeneration_error', 'user.errors');
                $this->setAttribute('errors', array('auth' => $error_message));

                $this->logError(
                    "[SESSIONID_REGENERATION_FAILED] SessionId could not be regenerated " . $log_message_part
                );
            }
        } else if ($auth_response->getState() === Auth\AuthResponse::STATE_UNAUTHORIZED) {
            $view_name = 'Error';

            $user->setAuthenticated(false);
            $this->setAttribute('errors', array('auth' => $translation_manager->_('invalid_login', 'user.errors')));

            $this->logError(
                sprintf(
                    "[UNAUTHORIZED] Authentication attempt failed %s\nErrors are: %s",
                    $log_message_part,
                    join(PHP_EOL, $auth_response->getErrors())
                )
            );
        } else {
            $view_name = 'Error';

            $user->setAuthenticated(false);
            $this->setAttribute('errors', array('auth' => $auth_response->getMessage()));

            $this->logError(
                sprintf(
                    "[UNAUTHORIZED] Authentication attempt failed with auth response being '%s' %s\nErrors are: %s",
                    $auth_response->getState(),
                    $log_message_part,
                    join(PHP_EOL, $auth_response->getErrors())
                )
            );
        }

        return $view_name;
    }

    protected function getAuthProvider()
    {
        $auth_provider_info = AgaviConfig::get('core.auth_provider');
        $options = array();
        $auth_provider_class = '';

        if (is_array($auth_provider_info)) {
            if (!isset($auth_provider_info['class'])) {
                throw new \InvalidArgumentException("Missing 'class' setting for auth provider config.");
            }

            $auth_provider_class = $auth_provider_info['class'];
            unset($auth_provider_info['class']);
            $options = $auth_provider_info;
        } else {
            $auth_provider_class = $auth_provider_info;
        }

        if (!class_exists($auth_provider_class, true)) {
            throw new \InvalidArgumentException('The configured auth_provider cannot be loaded.');
        }

        return new $auth_provider_class(
            new Config\ArrayConfig($options)
        );
    }

    protected function getUserAttributes(Auth\IAuthProvider $auth_provider, Auth\IAuthResponse $auth_response)
    {
        $user_attributes = array_merge(array('acl_role' => 'user'), $auth_response->getAttributes());

        if (isset($user_attributes['external_roles']) && is_array($user_attributes['external_roles'])) {
            foreach ($user_attributes['external_roles'] as $external_role) {
                $external_role = $this->getContext()->getUser()->mapExternalRoleToDomain(
                    $auth_provider->getTypeIdentifier(),
                    $external_role
                );
            }
        }

        return $user_attributes;
    }

    /**
     * Return whether this action requires authentication before execution.
     *
     * @return boolean false as login is not required for login attempts.
     */
    public function isSecure()
    {
        return false;
    }

    /**
     * @return string 'auth' as the default logger to use for `log<Level>()` calls
     */
    public function getLoggerName()
    {
        return 'auth';
    }
}
