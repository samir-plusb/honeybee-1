<?php

namespace Honeybee\Core\Security\Auth;

use Honeybee\Domain\User\UserModule;

/**
 * The StandardAuthProvider provides authentication against account information coming from the User module.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
class StandardAuthProvider implements IAuthProvider
{
    const TYPE_KEY = 'standard-auth';

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
        $module = UserModule::getInstance();
        $service = $module->getService();
        $userSearchSpec = array('filter' => array('username.filter' => $username));
        $result = $service->find($userSearchSpec, 0, 1);
        $user = NULL;

        if (1 === $result['totalCount'])
        {
            $user = $result['documents']->first();
            // @todo check workflow state and only allow active users to login.
        }
        else
        {
            return new AuthResponse(
                AuthResponse::STATE_UNAUTHORIZED,
                "authentication failed"
            );
        }
        $passwordHandler = new CryptedPasswordHandler();
        
        if ($passwordHandler->verify($password, $user->getPasswordHash()))
        {
            return new AuthResponse(
                AuthResponse::STATE_AUTHORIZED,
                "authenticaton success",
                array(
                    'login' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'acl_role' => $user->getRole(),
                    'name' => $user->getFirstname() . ' ' . $user->getLastname()
                )
            );
        }

        return new AuthResponse(
            AuthResponse::STATE_UNAUTHORIZED,
            "authentication failed"
        );
    }
}
