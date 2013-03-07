<?php

namespace Honeybee\Core\Security\Auth;

/**
 * The IAuthResponse specifies how authentication attempts shall be answered by IAuthProviders.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
interface IAuthResponse
{
    public function getMessage();

    public function getErrors();

    public function getState();

    public function getAttributes();

    public function getAttribute($name, $default = NULL);
}
