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
    public function getDefaultViewName()
    {
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
        $this->checkLdapConfig();

        $username = $parameters->getParameter("username");

        $logger = $this->getContext()
                ->getLoggerManager()
                ->getLogger('login');

        $this->ldap = ldap_connect(AgaviConfig::get("ldap.host"), AgaviConfig::get("ldap.port", 389));

        if (! $this->ldap)
        {
            //todo introduce a fallback action from config to allow other logins for dev environments.
            $errorMessage = "Can not connect to LDAP Server: " . AgaviConfig::get("ldap.host");
            $logger->log(new AgaviLoggerMessage($errorMessage));
            $this->setAttribute('error', $errorMessage);
            return 'Error';
        }

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, AgaviConfig::get("ldap.protocol", 3));

        $bindRdn =
            sprintf("%s=%s,%s", AgaviConfig::get("ldap.user_search", "uid"), $this->getLdapEscapedString($username),
                AgaviConfig::get("ldap.base_user"));

        if (! @ldap_bind($this->ldap, $bindRdn, $parameters->getParameter("password")))
        {
            if (0x31 == ldap_errno($this->ldap))
            {
                // LDAP_INVALID_CREDENTIALS
                $this->getContext()
                    ->getUser()
                    ->setAuthenticated(FALSE);

                $errorMessage =
                    $this->getContext()
                        ->getTranslationManager()
                        ->_('This combination of username and password is invalid.', 'auth.messages');
                $this->setAttribute('error', $errorMessage);
                $this->getContainer()
                    ->getValidationManager()
                    ->setError('username_password_mismatch', $errorMessage);

                $logger->log(
                        new AgaviLoggerMessage(
                            sprintf(
                                'Failed authentication attempt for username %1$s, username/password missmatch (%2$s)',
                                $username, ldap_error($this->ldap)), AgaviILogger::INFO));
            }
            else
            {
                $this->setAttribute('error', 'LDAP error: ' . ldap_error($this->ldap));
                $logger->log(
                        new AgaviLoggerMessage(
                            AgaviConfig::get("ldap.host") . ': LDAP error: ' . ldap_error($this->ldap)));
            }

            return 'Input';
        }

        $uid = $this->getLdapAttribute($username, "uid");

        if (AgaviConfig::has("ldap.group_required"))
        {
            $ldapDn =
                sprintf("%s=%s,%s", AgaviConfig::get("ldap.group_search"), AgaviConfig::get("ldap.group_required"),
                    AgaviConfig::get("ldap.base_group"));

            $filter =
                sprintf("(& (objectClass=%s) (%s=%s))", AgaviConfig::get("ldap.group_object_class", "posixGroup"),
                    AgaviConfig::get("ldap.group_member_attr", "memberUid"),
                    $this->getLdapEscapedString(
                            AgaviConfig::get("ldap.group_member_attr_is_dn", FALSE) ? $bindRdn : $uid));

            $entry = ldap_read($this->ldap, $ldapDn, $filter);

            if (! $entry)
            {
                throw new AgaviSecurityException(ldap_error($this->ldap));
            }

            $info = ldap_get_entries($this->ldap, $entry);

            if (! $info || 0 == $info["count"])
            {
                $this->getContext()
                    ->getUser()
                    ->setAuthenticated(FALSE);

                $translationManager = $this->getContext()
                        ->getTranslationManager();
                $errorMessage =
                    sprintf(
                        $translationManager->_('Failed authentication attempt for username %1$s, require group membership of "%2$s"'),
                        $username, AgaviConfig::get("ldap.group_required"));
                $this->setAttribute('error', $errorMessage);
                $this->getContainer()
                    ->getValidationManager()
                    ->setError('username_password_mismatch', $errorMessage);

                $logger->log(new AgaviLoggerMessage($errorMessage, AgaviILogger::INFO));

                return 'Input';
            }
        }

        $this->setUserAttributes($username);

        $logger->log(
                new AgaviLoggerMessage(sprintf('Successfull authentication attempt for username %1$s', $username),
                    AgaviILogger::INFO));

        return 'Success';
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
        $logger = $this->getContext()
                ->getLoggerManager()
                ->getLogger('login');

        $logger->log(
                new AgaviLoggerMessage(
                    sprintf('Failed authentication attempt for username %1$s, validation failed',
                        $parameters->getParameter('username')), AgaviILogger::INFO));

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
     * Checks if all required ldap settings are correctly configured.
     *
     * @staticvar   array $ldap_settings
     *
     * @throws      AgaviConfigurationException If the any required ldap setting can't ne resolved.
     */
    private function checkLdapConfig()
    {
        $missing = array();

        static $ldap_settings =
            array(
                "host",
                "base",
                "base_user",
                "base_group",
                "user_search",
                "group_search",
                "user_name_attr",
                "user_email_attr",
                "group_object_class",
                "group_member_attr",
                "group_name_attr"
            );

        foreach ($ldap_settings as $setting)
        {
            if (! AgaviConfig::has("ldap." . $setting))
            {
                $missing[] = "ldap." . $setting;
            }
        }

        if (!empty($missing))
        {
            throw new AgaviConfigurationException("Missing LDAP settings: " . join(", ", $missing));
        }
    }

    /**
     * Return the the ldap attribute value for the given
     * user and attribute name or FALSE if can't be resolved.
     *
     * @param       string $username
     * @param       string $attribute
     *
     * @return      mixed
     *
     * @uses        Auth_LoginAction::getLdapEscapedString()
     */
    private function getLdapAttribute($username, $attribute)
    {
        $ldapDn =
            sprintf("%s=%s,%s", AgaviConfig::get("ldap.user_search", "uid"), $this->getLdapEscapedString($username),
                AgaviConfig::get("ldap.base_user"));

        $filter = "(objectClass=*)";
        $entry = @ldap_read($this->ldap, $ldapDn, $filter, array(
                $attribute
            ));

        if ($entry)
        {
            $info = @ldap_get_entries($this->ldap, $entry);

            return empty($info[0][$attribute][0]) ? FALSE : $info[0][$attribute][0];
        }

        return FALSE;
    }

    /**
     * Returns a string which has the chars *, (, ), \ & NUL escaped to LDAP compliant
     * syntax as per RFC 2254.
     * Thanks and credit to Iain Colledge for the research and function.
     *
     * @param       string $string
     *
     * @return      string
     */
    private function getLdapEscapedString($string)
    {
        // Make the string LDAP compliant by escaping *, (, ) , \ & NUL
        return str_replace(array(
                "*", "(", ")", "\\", "\x00"
            ), //replace this
            array(
                "\\2a", "\\28", "\\29", "\\5c", "\\00"
            ), //with this
            $string //in this
        );
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
