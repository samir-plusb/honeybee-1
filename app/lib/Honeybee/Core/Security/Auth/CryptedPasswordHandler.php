<?php

namespace Honeybee\Core\Security\Auth;

class CryptedPasswordHandler implements IPasswordHandler
{
    public function __construct()
    {
        if (CRYPT_BLOWFISH !== 1)
        {
            throw new Exception("Missing blowfish support.");
        }
    }

    public function hash($password)
    {
        return crypt($password, $this->generateBcryptSalt());
    }

    public function verify($password, $challenge)
    {
        return crypt($password, $challenge) === $challenge;
    }

    protected function generateBcryptSalt()
    {
         return sprintf('$2a$%s$%s',
            str_pad(7, 2, '0', STR_PAD_LEFT),
            substr(
                strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'),
                0, 22
            )
        );
    }
}
