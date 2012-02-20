<?php

/**
 * The Auth_LoginAction class provides login support.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <Tom.Anheyer@BerlinOnline.de>
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Auth
 * @subpackage      Mvc
 */
class Auth_LoginAction extends AuthBaseAction
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
        // Forward to write if someone is passing our action the required parameters for logging in.
        if ($parameters->hasParameter('username') && $parameters->hasParameter('password'))
        {
            return $this->executeWrite($parameters);
        }
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

        if (AuthResponse::STATE_AUTHORIZED === $authResponse->getState())
        {
            $logger->log(
                new AgaviLoggerMessage("Successfull authentication attempt for username $username")
            );
            $userAttributes = array_merge(
                array('acl_role' => 'user'),
                $authResponse->getAttributes()
            );
            if (isset($userAttributes['external_role']))
            {
                $domainRole = $user->mapExternalRoleToDomain(
                    $authProvider->getTypeIdentifier(),
                    $userAttributes['external_role']
                );
                if ($domainRole)
                {
                    $userAttributes['acl_role'] = $domainRole;
                }
            }
            $user->setAttributes($userAttributes);
            $user->setAuthenticated(TRUE);
            return 'Success';
        }
        else if (AuthResponse::STATE_UNAUTHORIZED === $authResponse->getState())
        {
            $logger->log(
                new AgaviLoggerMessage(
                    join(PHP_EOL, $authResponse->getErrors())
                )
            );
            $errorMessage = $translationManager->_($authResponse->getMessage(), 'auth.messages');
            $this->getContainer()->getValidationManager()->setError(
                'username_password_mismatch',
                $errorMessage
            );
            $this->setAttribute('error', $errorMessage);
            $user->setAuthenticated(FALSE);
            return 'Input';
        }

        $errorMessage = join(PHP_EOL, $authResponse->getErrors());
        $logger->log(new AgaviLoggerMessage($errorMessage));
        $this->setAttribute('error', $authResponse->getMessage());
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
        $logger = $this->getContext()->getLoggerManager()->getLogger('login');
        $logger->log(
            new AgaviLoggerMessage(
                sprintf(
                    'Failed authentication attempt for username %1$s, validation failed',
                    $parameters->getParameter('username')
                )
            )
        );
        return 'Input';
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

?>
