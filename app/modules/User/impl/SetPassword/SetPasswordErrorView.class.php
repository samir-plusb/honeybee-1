<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 * @subpackage      SetPassword
 */
class User_SetPassword_SetPasswordErrorView extends UserBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Passwort setzen - Fehler');
    }
}
