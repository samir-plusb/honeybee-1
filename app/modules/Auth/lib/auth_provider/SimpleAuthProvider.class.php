<?php

class SimpleAuthProvider extends BaseAuthProvider
{
    private $accounts;

    public function __construct()
    {
        $this->accounts = AgaviConfig::get('core.simple_logins', array());
    }

    public function getTypeIdentifier()
    {
        return 'simple';
    }

    //put your code here
    public function authenticate($username, $password, $options = array())
    {
        $errors = array();

        if (isset($this->accounts[$username]) && $this->accounts[$username]['pwd'] === $password)
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

?>
