<?php

namespace Honeybee\Agavi\View;

use Dat0r\Core\Runtime\Field\ReferenceField;

class ListSuccessView extends BaseView
{
    /**
     * Handle presentation logic for the web (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $layout = $this->hasAttribute('referenceField') ? 'reference' : NULL;
        $this->setupHtml($parameters, $layout);
        $module = $this->getAttribute('module');

        $tm = $this->getContext()->getTranslationManager();
        $this->setAttribute('_title', $tm->_($module->getName(), 'modules.labels') . ': ' . $tm->_('List view', 'modules.labels'));

        $this->getLayer('content')->setSlot(
            'list',
            $this->createSlotContainer('Common', 'List', array(
                'config' => $this->getAttribute('config'),
                'state' => $this->getAttribute('state')
            ), NULL, 'read')
        );

        /* Will comment back in, when the stuff is completely styled.
        $this->getLayer('content')->setSlot(
            'sidebar',
            $this->createSlotContainer('Common', 'Sidebar', array('tree_modules' => $this->getSidebarTreeModules()), NULL, 'read')
        );
        */

        $this->setBreadcrumb();
    }

    /**
     * Handle presentation logic for the web (json).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return $this->createSlotContainer('Common', 'List', array(
            'config' => $this->getAttribute('config'),
            'state' => $this->getAttribute('state')
        ), NULL, 'read');
    }

    protected function setBreadcrumb()
    {
        $listState = $this->getAttribute('state');
        $module = $this->getAttribute('module');

        $listRouteName = sprintf('%s.list', $module->getOption('prefix'));
        $page = round($listState->getOffset() / $listState->getLimit()) + 1;
        $routing = $this->getContext()->getRouting();

        $tm = $this->getContext()->getTranslationManager();
        $moduleName = $tm->_($module->getName(), 'modules.labels');
        $moduleCrumb = array(
            'text' => $moduleName,
            'link' => $routing->gen($listRouteName),
            'info' => $moduleName . ' - ' . $tm->_('List view (start)', 'modules.labels'),
            'icon' => 'icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'honeybee.breadcrumbs', array());
        if (1 <= count($breadcrumbs))
        {
            array_splice($breadcrumbs, 1);
        }

        $routeParams = array(
            'offset' =>  $listState->getOffset(),
            'limit' => $listState->getLimit(),
            'sorting' => array(
                'field' => $listState->getSortField(),
                'direction' => $listState->getSortDirection()
            )
        );

        if (! $listState->hasSearch() && ! $listState->hasFilter())
        {
            $breadcrumbs = array(array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $moduleName . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'icon-list'
            ));
        }
        else if ($listState->hasSearch())
        {
            $routeParams['search'] = $listState->getSearch();
            $breadcrumbs[] = array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $tm->_('Search for:', 'modules.labels') . ' ' . $listState->getSearch() . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'icon-search'
            );
        }
        else if ($listState->hasFilter())
        {
            $routeParams['filter'] = $listState->getFilter();
            $breadcrumbs[] = array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $tm->_('Extended Search', 'modules.labels') . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'icon-search'
            );
        }

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }

    protected function getSidebarTreeModules()
    {
        $module = $this->getAttribute('module');
        $modules = array();

        foreach ($module->getFields() as $field)
        {
            if ($field instanceof ReferenceField)
            {
                $referencedModules = $field->getReferencedModules();
                foreach ($referencedModules as $module)
                {
                    if ($module->isActingAsTree())
                    {
                        $modules[] = get_class($module);
                    }
                }
            } 
        }

        return $modules;
    }
}
