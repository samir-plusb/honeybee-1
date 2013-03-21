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
        $this->setupHtml($parameters);
        $module = $this->getAttribute('module');

        $this->setAttribute('_title', sprintf('Honeybee - %s: Liste', $module->getName()));

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

        $moduleCrumb = array(
            'text' => $module->getName(),
            'link' => $routing->gen($listRouteName),
            'info' => sprintf('%s - Listenansicht (Anfang)', $module->getName()),
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
                'text' => $module->getName(),
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $module->getName() . ' (Seite ' . $page . ')',
                'icon' => 'icon-list'
            ));
        }
        else if ($listState->hasSearch())
        {
            $routeParams['search'] = $listState->getSearch();
            $breadcrumbs[] = array(
                'text' => $module->getName(),
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => 'Suche nach: ' . $listState->getSearch() . ' (Seite ' . $page . ')',
                'icon' => 'icon-search'
            );
        }
        else if ($listState->hasFilter())
        {
            $routeParams['filter'] = $listState->getFilter();
            $breadcrumbs[] = array(
                'text' => $module->getName(),
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => 'Erweiterte Suche (Seite ' . $page. ')',
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
