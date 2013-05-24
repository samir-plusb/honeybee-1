<?php

use Honeybee\Core\Dat0r\ModuleService;

class Common_Header_HeaderSuccessView extends CommonBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $user = $this->getContext()->getUser();

        if ($user->isAuthenticated())
        {
            $email = $user->getAttribute('email');
            $url = AgaviConfig::get('core.gravatar_url_tpl');
            $hash = md5('12345');

            if ($email)
            {
                $hash = md5(strtolower(trim($email)));
            }

            $url = str_replace('{EMAIL_HASH}', $hash, $url);
            $this->setAttribute('avatar_url', $url);

            $menuProviderClass = AgaviConfig::get('core.menu_data_provider', 'MenuDataProvider');
            $menuProvider = new $menuProviderClass();
            $this->setAttribute('modules', $menuProvider->getMenuData());
        }
    }
}
