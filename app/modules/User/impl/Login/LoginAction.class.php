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
     * @param AgaviParameterHolder $parameters
     *
     * @return string The name of the view to execute.
     */
    public function executeRead(AgaviParameterHolder $parameters)
    {
        // Forward to write if someone is passing our action the required parameters for logging in. (basic auth)
        if ($parameters->hasParameter('username') && $parameters->hasParameter('password'))
        {
            return $this->executeWrite($parameters);
        }

        $this->setAttribute('reset_support_enabled', \AgaviConfig::get('user.module_active', FALSE));

        return 'Input';
    }

    /**
     * Try to login based on the account information, that is provided with our given $rd.
     *
     * @param       AgaviParameterHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviParameterHolder $parameters)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('login');
        $translationManager = $this->getContext()->getTranslationManager();
        $user = $this->getContext()->getUser();

        $username = $parameters->getParameter('username');
        $password = $parameters->getParameter('password');
        $authProviderClass = AgaviConfig::get('core.auth_provider');

        if (! class_exists($authProviderClass, TRUE))
        {
            throw new InvalidArgumentException('The configured auth provider can not be loaded');
        }

        $authProvider = new $authProviderClass();
        $authResponse = $authProvider->authenticate($username, $password);

        if (Auth\AuthResponse::STATE_AUTHORIZED === $authResponse->getState())
        {
            $logger->log(
                new AgaviLoggerMessage("Successfull authentication attempt for username $username")
            );

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

            $logger->log(
                new AgaviLoggerMessage(join(PHP_EOL, $authResponse->getErrors()))
            );

            $errorMessage = $translationManager->_('invalid_login', 'user.messages');
            $this->setAttribute('errors', array('auth' => $errorMessage));

            return 'Error';
        }

        $errorMessage = join(PHP_EOL, $authResponse->getErrors());
        $logger->log(new AgaviLoggerMessage($errorMessage));

        $this->setAttribute('error', array('auth' => $authResponse->getMessage()));
        $user->setAuthenticated(FALSE);

        return 'Error';
    }

    /**
     * This method handles validation errors that occur upon our received input data.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function handleError(AgaviRequestDataHolder $parameters)
    {
        $translationManager = $this->getContext()->getTranslationManager();
        $logger = $this->getContext()->getLoggerManager()->getLogger('login');
        $logger->log(
            new AgaviLoggerMessage(
                sprintf(
                    'Failed authentication attempt for username %1$s, validation failed',
                    $parameters->getParameter('username')
                )
            )
        );
        
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrors() as $field => $error)
        {
            $errors[$field] = $error['messages'][0];
        }

        $errors['auth'] = $translationManager->_('invalid_login', 'user.messages');
        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    /**
     * Return whether this action requires authentication
     * before execution.
     *
     * @return      boolean
     */
    public function isSecure()
    {
        return FALSE;
    }
}
