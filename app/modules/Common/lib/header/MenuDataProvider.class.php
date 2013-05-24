<?php

use Honeybee\Core\Dat0r\ModuleService;
use Honeybee\Core\Dat0r\Module;

class MenuDataProvider
{
    public function getMenuData()
    {
        $modules = array();
        $service = new ModuleService();

        foreach ($service->getModules() as $module)
        {
            $modules[$module->getName()] = array(
                'label' => $this->getModuleLabel($module),
                'links' => $this->getModuleLinks($module)
            );
        }

        // sort modules by their translated labels
        uasort($modules, function($left, $right)
        {
            return strcmp($left['label'], $right['label']);
        });

        // apply custom sort order if specified in project/config/settings.xml
        $customModuleSortOrder = AgaviConfig::get('project.modules.sort_order', array());
        $sortedModules = array();

        foreach ($customModuleSortOrder as $moduleName)
        {
            if (isset($modules[$moduleName]))
            {
                $sortedModules[$moduleName] = $modules[$moduleName];
            }   
        }

        // add all modules that were not specified in project/config/settings.xml
        foreach ($modules as $moduleName => $values)
        {
            if (!isset($sortedModules[$moduleName]))
            {
                $sortedModules[$moduleName] = $modules[$moduleName];
            }
        }

        return $sortedModules;
    }

    protected function getModuleLabel(Module $module)
    {
        $context = AgaviContext::getInstance();
        $translationManager = $context->getTranslationManager();

        return $translationManager->_($module->getName(), 'modules.labels');
    }

    protected function getModuleLinks(Module $module)
    {
        $context = AgaviContext::getInstance();
        $translationManager = $context->getTranslationManager();
        $routing = $context->getRouting();
        $user = $context->getUser();

        $moduleLinks = array();

        if ($user->isAllowed($module, $module->getOption('prefix') . '::create'))
        {
            $moduleLinks[] = array(
                'name' => 'create_link', 
                'url' => $routing->gen($module->getOption('prefix') . '.edit'),
                'icon_class' => 'hb-icon-file-4',
                'label' => $translationManager->_('neuer Eintrag')
            );
        }

        if ($user->isAllowed($module, $module->getOption('prefix') . '::read'))
        {
            $moduleLinks[] = array(
                'name' => 'list_link', 
                'url' => $routing->gen($module->getOption('prefix') . '.list'),
                'icon_class' => 'hb-icon-list-3',
                'label' => $translationManager->_('Übersicht')
            );

            if ($module->isActingAsTree())
            {
                $moduleLinks[] = array(
                    'name' => 'tree_link', 
                    'url' => $routing->gen($module->getOption('prefix') . '.tree'),
                    'icon_class' => 'hb-icon-tree',
                    'label' => $translationManager->_('Baumansicht')
                );
            }
        }

        return $moduleLinks;
    }
}
