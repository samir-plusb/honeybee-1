<?php

use Honeybee\Core\Service\DocumentService;
use Honeybee\Core\Security\Auth\TokenGenerator;

use Honeybee\Domain\User\UserDocument;

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @package User
 */
class UserService extends DocumentService
{
    public function sendPasswordLostEmail(UserDocument $user)
    {
        $user->setAuthToken(TokenGenerator::generateToken());
        $date = new DateTime();
        $date->add(new DateInterval('PT2H'));
        $user->setTokenExpireDate($date->format(DATE_ISO8601));
        $this->save($user);

        // @todo send password email via swift
    }
}
