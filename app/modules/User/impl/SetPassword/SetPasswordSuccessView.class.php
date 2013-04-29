<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 * @subpackage      SetPassword
 */
class User_SetPassword_SetPasswordSuccessView extends UserBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Passwort erfolgreich gesetzt.');
    }
}
