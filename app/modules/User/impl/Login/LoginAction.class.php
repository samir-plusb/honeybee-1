<?php

use Honeybee\Core\Security\Auth;

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

        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', FALSE));

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
        $tm = $this->getContext()->getTranslationManager();
        $user = $this->getContext()->getUser();

        $username = $request_data->getParameter('username');
        $password = $request_data->getParameter('password');
        $authProviderClass = AgaviConfig::get('core.auth_provider');

        if (! class_exists($authProviderClass, TRUE))
        {
            throw new InvalidArgumentException('The configured auth_provider can not be loaded.');
        }

        $authProvider = new $authProviderClass();
        $authResponse = $authProvider->authenticate($username, $password);

        $log_message_part = "for username '$username' via auth provider '$authProviderClass'.";
        if (Auth\AuthResponse::STATE_AUTHORIZED === $authResponse->getState())
        {
            $this->logInfo("[AUTHORIZED] Successful authentication attempt " . $log_message_part);

            $userAttributes = array_merge(
                array('acl_role' => 'user'),
                $authResponse->getAttributes()
            );

            if (isset($userAttributes['external_roles']) && is_array($userAttributes['external_roles']))
            {
                foreach ($userAttributes['external_roles'] as $externalRole)
                {
                    $domainRole = $user->mapExternalRoleToDomain(
                        $authProvider->getTypeIdentifier(),
                        $externalRole
                    );

                    if ($domainRole)
                    {
                        $userAttributes['acl_role'] = $domainRole;
                        break;
                    }
                }
            }

            $user->setAttributes($userAttributes);
            $user->setAuthenticated(TRUE);

            return 'Success';
        }
        else if (Auth\AuthResponse::STATE_UNAUTHORIZED === $authResponse->getState())
        {
            $user->setAuthenticated(FALSE);

            $this->logError("[UNAUTHORIZED] Authentication attempt failed " . $log_message_part . " Errors are: " . join(PHP_EOL, $authResponse->getErrors()));

            $errorMessage = $tm->_('invalid_login', 'user.messages');
            $this->setAttribute('errors', array('auth' => $errorMessage));

            return 'Error';
        }

        $this->logError("[UNAUTHORIZED] Authentication attempt failed with auth response being '" . $authResponse->getState() . "' " . $log_message_part . " Errors are: " . join(PHP_EOL, $authResponse->getErrors()));

        $this->setAttribute('error', array('auth' => $authResponse->getMessage()));
        $user->setAuthenticated(FALSE);

        return 'Error';
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
        $tm = $this->getContext()->getTranslationManager();
        $vm = $this->getContainer()->getValidationManager();

        $this->logError("[UNAUTHORIZED] Failed authentication attempt for username '", $request_data->getParameter('username'), "' - validation failed:", $vm);

        $errors = array();
        foreach ($vm->getErrors() as $field => $error)
        {
            $errors[$field] = $error['messages'][0];
        }

        $errors['auth'] = $tm->_('invalid_login', 'user.messages');
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
        return FALSE;
    }

    /**
     * @return string 'auth' as the default logger to use for `log<Level>()` calls
     */
    public function getLoggerName()
    {
        return 'auth';
    }
}
