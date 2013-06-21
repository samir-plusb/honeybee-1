<?php

use Honeybee\Agavi\View\EditSuccessView;

class User_Edit_EditSuccessView extends EditSuccessView
{
    public function executeConsole(AgaviRequestDataHolder $request_data)
    {
        $user = $request_data->getParameter('document');

        $setPasswordUrl = sprintf(
            '%suser/password?token=%s',
            Honeybee\Core\Environment::getBaseHref(),
            $user->getAuthToken()
        );

        return 'Please set a password for the created account at: ' . 
            PHP_EOL . $setPasswordUrl . PHP_EOL;
    }
}
