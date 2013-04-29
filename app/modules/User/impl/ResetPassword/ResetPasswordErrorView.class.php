<?php

use Pulq\Agavi\View\BaseErrorView;

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_ResetPassword_ResetPasswordErrorView extends UserBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        
        $this->setAttribute('_title', 'Fehler beim Passwort zurücksetzen');
    }
}
