<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 * @subpackage      SetPassword
 */
class User_SetPassword_SetPasswordInputView extends UserBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $this->setAttribute('token', $parameters->getParameter('token'));
        $this->setAttribute('_title', 'Passwort setzen');
    }
}
