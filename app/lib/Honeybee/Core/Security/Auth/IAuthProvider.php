<?php

namespace Honeybee\Core\Security\Auth;

/**
 * The IAuthProvider specifies how authentication shall be exposed to consuming components
 * inside the Auth module.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
interface IAuthProvider
{
    public function getTypeKey();

    public function authenticate($username, $password, $options = array());
}
