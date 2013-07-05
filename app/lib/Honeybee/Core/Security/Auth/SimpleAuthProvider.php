<?php

namespace Honeybee\Core\Security\Auth;

use Honeybee\Core\Config\IConfig;

/**
 * The SimpleAuthProvider provides authentication against xml based account information.
 * The accounts used by te simple auth provider are configured inside the settings.xml.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
class SimpleAuthProvider implements IAuthProvider
{
    const TYPE_KEY = 'simple-auth';

    private $accounts;

    protected $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
        $this->accounts = \AgaviConfig::get('core.simple_logins', array());
    }

    public function getTypeKey()
    {
        return static::TYPE_KEY;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function authenticate($username, $password, $options = array()) // @codingStandardsIgnoreEnd
    {
var_dump("asdasddas");exit;
        $errors = array();
        $passwordHandler = new CryptedPasswordHandler();

        if (isset($this->accounts[$username]) &&
            isset($this->accounts[$username]['pwd_hash']) &&
            $passwordHandler->verify($password, $this->accounts[$username]['pwd_hash'])
        )
        {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                "authenticaton success",
                $this->accounts[$username]['attributes']
            );
        }

        return new AuthResponse(
            AuthResponse::STATE_UNAUTHORIZED,
            "authentication failed",
            array(),
            $errors
        );
    }
}
