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
        // Forward to write if someone is passing our action the required parameters for logging in. (basic auth)
        if ($request_data->hasParameter('username') && $request_data->hasParameter('password'))
        {
            return $this->executeWrite($request_data);
        }

        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', false));

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
        $view_name = '';
        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', false));

        $translation_manager = $this->getContext()->getTranslationManager();
        $user = $this->getContext()->getUser();

        $auth_provider = $this->getAuthProvider();
        $username = $request_data->getParameter('username');
        $auth_response = $auth_provider->authenticate($username, $request_data->getParameter('password'));

        $log_message_part = sprintf(
            "for username '$username' via auth provider %s.",
            get_class($auth_provider)
        );
        if ($auth_response->getState() === Auth\AuthResponse::STATE_AUTHORIZED)
        {
            $user->setAuthenticated(true);
            $view_name = 'Success';

            $this->logInfo("[AUTHORIZED] Successful authentication attempt " . $log_message_part);

            $user_attributes = $this->getUserAttributes($auth_provider, $auth_response);
            $user->setAttributes($user_attributes);
        }
        else if ($auth_response->getState() === Auth\AuthResponse::STATE_UNAUTHORIZED)
        {
            $user->setAuthenticated(false);
            $view_name = 'Error';

            $this->logError(
                sprintf(
                    "[UNAUTHORIZED] Authentication attempt failed %s\nErrors are: %s",
                    $log_message_part,
                    join(PHP_EOL, $auth_response->getErrors())
                )
            );

            $error_message = $translation_manager->_('invalid_login', 'user.messages');
            $this->setAttribute('errors', array('auth' => $error_message));
        }
        else
        {
            $user->setAuthenticated(false);
            $view_name = 'Error';

            $this->logError(
                sprintf(
                    "[UNAUTHORIZED] Authentication attempt failed with auth response being '%s' %s\nErrors are: %s",
                    $auth_response->getState(),
                    $log_message_part,
                    join(PHP_EOL, $auth_response->getErrors())
                )
            );

            $this->setAttribute('errors', array('auth' => $auth_response->getMessage()));
        }

        return $view_name;
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
        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', FALSE));

        $translation_manager = $this->getContext()->getTranslationManager();
        $validation_manager = $this->getContainer()->getValidationManager();

        $this->logError(
            "[UNAUTHORIZED] Failed authentication attempt for username '",
            $request_data->getParameter('username'),
            "' - validation failed:",
            $validation_manager
        );

        $errors = array();
        foreach ($validation_manager->getErrors() as $field => $error)
        {
            $errors[$field] = $error['messages'][0];
        }

        $errors['auth'] = $translation_manager->_('invalid_login', 'user.messages');
        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    /**
     * Return whether this action requires authentication
     * before execution.
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

    protected function getAuthProvider()
    {
        $auth_provider_info = AgaviConfig::get('core.auth_provider');
        $options = array();
        $auth_provider_class = '';

        if (is_array($auth_provider_info))
        {
            if (! isset($auth_provider_info['class']))
            {
                throw new InvalidArgumentException("Missing 'class' setting for auth provider config.");
            }

            $auth_provider_class = $auth_provider_info['class'];
            unset($auth_provider_info['class']);
            $options = $auth_provider_info;
        }
        else
        {
            $auth_provider_class = $auth_provider_info;
        }

        if (! class_exists($auth_provider_class, true))
        {
            throw new InvalidArgumentException('The configured auth_provider can not be loaded.');
        }

        return new $auth_provider_class(
            new Config\ArrayConfig($options)
        );
    }

    protected function getUserAttributes(Auth\IAuthProvider $auth_provider, Auth\IAuthResponse $auth_response)
    {
        $user = $this->getContext()->getUser();

        $user_attributes = array_merge(
            array('acl_role' => 'user'),
            $auth_response->getAttributes()
        );

        if (isset($user_attributes['external_roles']) && is_array($user_attributes['external_roles']))
        {
            foreach ($user_attributes['external_roles'] as $external_role)
            {
                $external_role = $user->mapExternalRoleToDomain(
                    $auth_provider->getTypeIdentifier(),
                    $external_role
                );

                if ($domain_role)
                {
                    $user_attributes['acl_role'] = $domain_role;
                    break;
                }
            }
        }

        return $user_attributes;
    }
}
