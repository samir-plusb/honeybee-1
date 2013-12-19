<?php

use Honeybee\Core\Dat0r\ModuleService;
use Honeybee\Core\Dat0r\Module;

class MenuDataProvider
{
    public function getMenuData()
    {
        $modules = array();
        $service = new ModuleService();

        foreach ($service->getModules() as $module) {
            $modules[$module->getName()] = array(
                'label' => $this->getModuleLabel($module),
                'links' => $this->getModuleLinks($module)
            );
        }

        // sort modules by their translated labels
        uasort(
            $modules,
            function($left, $right)
            {
                return strcmp($left['label'], $right['label']);
            }
        );

        // apply custom sort order if specified in project/config/settings.xml
        $custom_module_sort_order = AgaviConfig::get('project.modules.sort_order', array());
        $sorted_modules = array();

        foreach ($custom_module_sort_order as $module_name) {
            if (isset($modules[$module_name])) {
                $sorted_modules[$module_name] = $modules[$module_name];
            }
        }

        // add all modules that were not specified in project/config/settings.xml
        foreach ($modules as $module_name => $values) {
            if (!isset($sorted_modules[$module_name])) {
                $sorted_modules[$module_name] = $modules[$module_name];
            }
        }

        return $sorted_modules;
    }

    protected function getModuleLabel(Module $module)
    {
        $context = AgaviContext::getInstance();
        $translation_manager = $context->getTranslationManager();

        return $translation_manager->_($module->getName(), 'modules.labels');
    }

    protected function getModuleLinks(Module $module)
    {
        $context = AgaviContext::getInstance();
        $translation_manager = $context->getTranslationManager();
        $routing = $context->getRouting();
        $user = $context->getUser();

        $module_links = array();

        if ($user->isAllowed($module, $module->getOption('prefix') . '::create')) {
            $module_links[] = array(
                'name' => 'create_link',
                'url' => $routing->gen($module->getOption('prefix') . '.edit'),
                'icon_class' => 'hb-icon-file-4',
                'label' => $translation_manager->_('Neuer Eintrag')
            );
        }

        if ($user->isAllowed($module, $module->getOption('prefix') . '::read')) {
            $module_links[] = array(
                'name' => 'list_link',
                'url' => $routing->gen($module->getOption('prefix') . '.list'),
                'icon_class' => 'hb-icon-list-3',
                'label' => $translation_manager->_('Übersicht')
            );

            if ($module->isActingAsTree()) {
                $module_links[] = array(
                    'name' => 'tree_link',
                    'url' => $routing->gen($module->getOption('prefix') . '.tree'),
                    'icon_class' => 'hb-icon-tree',
                    'label' => $translation_manager->_('Baumansicht')
                );
            }
        }

        return $module_links;
    }
}
