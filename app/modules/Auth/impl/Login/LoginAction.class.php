<?php

/**
 * The Auth_LoginAction class provides login support to the companies ldap.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <Tom.Anheyer@BerlinOnline.de>
 * @package         Auth
 * @subpackage      Mvc
 */
class Auth_LoginAction extends AuthBaseAction
{
    /**
     * Holds our LDAP link_identifier resource.
     *
     * @var         resource
     */
    private $ldap;

    /**
     * This method returns the View name in case the Action doesn't serve the
     * current Request method.
     *
     * !!!!!!!!!! DO NOT PUT ANY LOGIC INTO THIS METHOD !!!!!!!!!!
     *
     * @return     mixed - A string containing the view name associated with this
     *                     action, or...
     *                   - An array with two indices:
     *                     0. The parent module of the view that will be executed.
     *                     1. The view that will be executed.
     *
     */
    public function executeRead(AgaviParameterHolder $parameters)
    {
        if ($parameters->hasParameter('username') && $parameters->hasParameter('password'))
        {
            error_log("Redirecting to write simple login.");
            return $this->executeWrite($parameters);
        }
        return 'Input';
    }

    /**
     * Try to login based on the account information, that is provided with our given $rd.
     *
     * @todo        Fallback to standard login action if ldap server is not available
     *
     * @param       AgaviParameterHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @uses        Auth_LoginAction::checkLdapConfig()
     * @uses        Auth_LoginAction::getLdapEscapedString()
     * @uses        Auth_LoginAction::getLdapAttribute()
     *
     * @todo Map a given ldap group to the corresponding domain role
     */
    public function executeWrite(AgaviParameterHolder $parameters)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('login');
        $translationManager = $this->getContext()->getTranslationManager();
        $user = $this->getContext()->getUser();

        $username = $parameters->getParameter("username");
        $password = $parameters->getParameter("password");
        $authProviderClass = AgaviConfig::get('core.auth_provider');
        if (! class_exists($authProviderClass, TRUE))
        {
            throw new InvalidArgumentException("The configured auth provider can not be loaded");
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

    /**
     * set user info attributes
     *
     * @param string $username
     */
    protected function setUserAttributes($username)
    {
        $user = $this->getContext()
                ->getUser();
        if (! $user instanceof AgaviISecurityUser)
        {
            return;
        }

        $user->setAuthenticated(TRUE);
        $attr =
            array(
                'login' => $username,
                'name' => $this->getLdapAttribute($username, AgaviConfig::get("ldap.user_name_attr", "cn")),
                'email' => $this->getLdapAttribute($username, AgaviConfig::get("ldap.user_email_attr", "mail")),
                'acl_role' => 'user' // default user without privleges
            );
        $user->setAttributes($attr);

        /*
         * Find groups of user
         */
        $distinguishedName =
            AgaviConfig::get("ldap.group_member_attr_is_dn")
                ? sprintf("%s=%s,%s", AgaviConfig::get("ldap.user_search", "uid"),
                    $this->getLdapEscapedString($username), AgaviConfig::get("ldap.base_user"))
                : $this->getLdapAttribute($username, "uid");

        $filter =
            sprintf("(& (objectClass=%s) (%s=%s))", AgaviConfig::get("ldap.group_object_class", "posixGroup"),
                AgaviConfig::get("ldap.group_member_attr", "memberUid"), $this->getLdapEscapedString($distinguishedName));

        $entry =
            ldap_search($this->ldap, AgaviConfig::get("ldap.base_group"), $filter,
                array(
                    AgaviConfig::get("ldap.group_name_attr")
                ));
        if (! $entry)
        {
            throw new AgaviSecurityException(ldap_error($this->ldap));
        }

        $info = ldap_get_entries($this->ldap, $entry);
        if (empty($info['count']))
        {
            return;
        }

        foreach ($info as $val)
        {
            if (!empty($val[AgaviConfig::get("ldap.group_name_attr")][0]))
            {
                $ldapRole = $val[AgaviConfig::get("ldap.group_name_attr")][0];
                $user->addCredential($ldapRole);

                if (($domainRole = $user->mapExternalRoleToDomain('ldap_group', $ldapRole)))
                {
                    // @todo Define how multiple roles are mapped.
                    $user->setAttribute('acl_role', $domainRole);
                }
            }
        }
    }
}
