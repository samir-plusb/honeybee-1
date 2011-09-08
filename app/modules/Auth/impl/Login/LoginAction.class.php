<?php

/**
 * The Auth_LoginAction class provides login support to the companies ldap.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <Tom.Anheyer@BerlinOnline.de>
 * @package         ApplicationBase
 * @subpackage      Auth
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
     * Try to login based on the account information, that is provided with our given $parameters.
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
     */
    public function executeWrite(AgaviParameterHolder $parameters) 
    {
        $this->checkLdapConfig();

        $username = $parameters->getParameter("username");

        $logger = $this->getContext()->getLoggerManager()->getLogger('login');

        $this->ldap = ldap_connect(AgaviConfig::get("ldap.host"), AgaviConfig::get("ldap.port", 389));
        
        if (!$this->ldap) 
        {
            $logger->log(
                new AgaviLoggerMessage("Can not connect to LDAP Server: " . AgaviConfig::get("ldap.host"))
            );
            //todo introduce a fallback action from config to allow other logins for dev environments.
            return 'Error';
        }
        
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, AgaviConfig::get("ldap.protocol", 3));

        $bind_rdn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.user_search", "uid"),
            $this->getLdapEscapedString($username),
            AgaviConfig::get("ldap.base_user")
        );
        
        if (!@ldap_bind($this->ldap, $bind_rdn, $parameters->getParameter("password"))) 
        {
            if (0x31 == ldap_errno($this->ldap)) 
            {
                // LDAP_INVALID_CREDENTIALS
                $this->getContext()->getUser()->setAuthenticated(false);
                $this->getContainer()->getValidationManager()->setError('username_password_mismatch', $this->getContext()->getTranslationManager()->_('This combination of username and password is invalid.', 'cms.auth'));
                
                $logger->log(
                    new AgaviLoggerMessage(
                        sprintf(
                            'Failed authentication attempt for username %1$s, username/password missmatch (%2$s)',
                            $username,
                            ldap_error($this->ldap)
                        ),
                        AgaviILogger::INFO
                    )
                );
                
                return 'Error';
            }
            
            $logger->log(new AgaviLoggerMessage(AgaviConfig::get("ldap.host") . ': LDAP error: ' . ldap_error($this->ldap)));
            
            //todo introduce a fallback action from config to allow other logins for dev environments.
            return 'Error';
        }

        $uid = $this->getLdapAttribute($username, "uid");
        
        if (AgaviConfig::has("ldap.group_required")) 
        {
            $dn = sprintf(
                "%s=%s,%s",
                AgaviConfig::get("ldap.group_search"),
                AgaviConfig::get("ldap.group_required"),
                AgaviConfig::get("ldap.base_group")
            );
            
            $filter = sprintf(
                "(& (objectClass=%s) (%s=%s))",
                AgaviConfig::get("ldap.group_object_class", "posixGroup"),
                AgaviConfig::get("ldap.group_member_attr", "memberUid"),
                $this->getLdapEscapedString(
                    AgaviConfig::get("ldap.group_member_attr_is_dn", false) ? $bind_rdn : $uid
                )
            );
            
            $entry = ldap_read($this->ldap, $dn, $filter);
            
            if (!$entry) 
            {
                throw new AgaviSecurityException(ldap_error($this->ldap));
            }
            
            $info = ldap_get_entries($this->ldap, $entry);
            
            if (!$info || 0 == $info["count"]) 
            {
                $this->getContext()->getUser()->setAuthenticated(false);
                $this->getContainer()->getValidationManager()->setError(
                    'username_password_mismatch', 
                    $this->getContext()->getTranslationManager()->_(
                        'This combination of username and password is invalid.',
                        'auth.errors'
                    )
                );
                
                $logger->log(
                    new AgaviLoggerMessage(
                        sprintf(
                            'Failed authentication attempt for username %1$s, require group membership of "%2$s"',
                            $username,
                            AgaviConfig::get("ldap.group_required")
                        ), 
                        AgaviILogger::INFO
                    )
                );
                
                return 'Error';
            }
        }

        $this->getContext()->getUser()->setAuthenticated(true);

        $logger->log(
            new AgaviLoggerMessage(
                sprintf('Successfull authentication attempt for username %1$s', $username),
                AgaviILogger::INFO
            )
        );

        return 'Success';
    }
    
    /**
     * This method handles validation errors that occur upon our received input data.
     * 
     * @param       AgaviRequestDataHolder $rd
     * 
     * @return      string The name of the view to execute.
     */
    public function handleError(AgaviRequestDataHolder $rd) 
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('login');
        
        $logger->log(
            new AgaviLoggerMessage(
                sprintf(
                    'Failed authentication attempt for username %1$s, validation failed',
                    $rd->getParameter('username')
                ),
                AgaviILogger::INFO
            )
        );

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
        
        static $ldap_settings = array(
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
            if (!AgaviConfig::has("ldap." . $setting))
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
     * user and attribute name or false if can't be resolved.
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
        $dn = sprintf(
            "%s=%s,%s",
            AgaviConfig::get("ldap.user_search", "uid"),
            $this->getLdapEscapedString($username), 
            AgaviConfig::get("ldap.base_user")
        );
        
        $filter = "(objectClass=*)";
        $entry = @ldap_read($this->ldap, $dn, "(objectClass=*)", array($attribute));
        
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
        return str_replace(
            array("*", "(", ")", "\\", "\x00"), //replace this
            array("\\2a", "\\28", "\\29", "\\5c", "\\00"), //with this
            $string //in this
        );
    }
}

?>