<?php

namespace Honeybee\Agavi\View;

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
            )), NULL, 'read'
        );

        $this->setBreadcrumb();
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

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'midas.breadcrumbs', array());
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

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }
}
