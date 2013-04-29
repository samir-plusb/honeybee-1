<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_ResetPassword_ResetPasswordSuccessView extends UserBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        
        $this->setAttribute('_title', 'Passwort zurücksetzen');
    }
}
