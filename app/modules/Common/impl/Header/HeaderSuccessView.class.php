<?php

use \Honeybee\Core\Dat0r\ModuleService;
use \Honeybee\Core\Util\Http\CurlFactory;

class Common_Header_HeaderSuccessView extends CommonBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $user = $this->getContext()->getUser();
        $email = $user->getAttribute('email');
        $url = AgaviConfig::get('core.gravatar_url_tpl');
        $hash = md5('12345');
        if ($email)
        {
            $hash = md5(strtolower(trim($email)));
        }

        $url = str_replace('{EMAIL_HASH}', $hash, $url);
        $curl = CurlFactory::create($url);
        $resp = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 === $status)
        {
            $this->setAttribute('avatar_url', $url);
        }

        $routing = $this->getContext()->getRouting();
        $service = new ModuleService();

        $modules = array();
        foreach ($service->getModules() as $module)
        {
            $modules[$module->getName()] = array(
                'list_link' => $routing->gen($module->getOption('prefix') . '.list'),
                'create_link' => $routing->gen($module->getOption('prefix') . '.edit'),
                'module_label' => $this->getContext()->getTranslationManager()->_($module->getName(), 'modules.labels')
            );
        }

        // sort modules by their translated labels
        uasort($modules, function($left, $right)
        {
            return strcmp($left['module_label'], $right['module_label']);
        });

        // apply custom sort order if specified in project/config/settings.xml
        $customModuleSortOrder = AgaviConfig::get('project.modules.sort_order', array());
        $sortedModules = array();
        foreach ($customModuleSortOrder as $moduleName)
        {
            $sortedModules[$moduleName] = $modules[$moduleName];
        }

        // add all modules that were not specified in project/config/settings.xml
        foreach ($modules as $moduleName => $values)
        {
            if (!isset($sortedModules[$moduleName]))
            {
                $sortedModules[$moduleName] = $modules[$moduleName];
            }
        }

        unset($modules);

        $this->setAttribute('modules', $sortedModules);
    }
}
