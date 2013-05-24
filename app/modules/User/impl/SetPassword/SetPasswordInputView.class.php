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
            'min_decimal_numbers' => (int)AgaviConfig::get('password_constraints.min_decimal_numbers', 2),
            'min_uppercase_chars' => (int)AgaviConfig::get('password_constraints.min_uppercase_chars', 2),
            'min_lowercase_chars' => (int)AgaviConfig::get('password_constraints.min_lowercase_chars', 2),
            'min_string_length' => (int)AgaviConfig::get('password_constraints.min_string_length', 10),
            'max_string_length' => (int)AgaviConfig::get('password_constraints.max_string_length', 32)
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
