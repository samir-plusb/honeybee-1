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

        $pwdRequirements = array(
            'min_decimal_numbers' => AgaviConfig::get('pwd_security.min_decimal_numbers'),
            'min_uppercase_chars' => AgaviConfig::get('pwd_security.min_uppercase_chars'),
            'min_lowercase_chars' => AgaviConfig::get('pwd_security.min_lowercase_chars'),
            'min_string_length' => AgaviConfig::get('pwd_security.min_string_length'),
            'max_string_length' => AgaviConfig::get('pwd_security.max_string_length')
        );

        $passwordMeterOptions = array(
            'requirements' => $pwdRequirements,
            'popover' => array(
                'pos' => 'right',
                'title' => 'Kennwortrichtlinien',
                'tpl_selector' => '#password_requirements'
            )
        );

        $this->setAttribute('password_meter_options', $passwordMeterOptions);
    }
}
