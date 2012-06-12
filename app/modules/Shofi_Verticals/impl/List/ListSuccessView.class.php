<?php

/**
 * The Shofi_Verticals_List_ListSuccessView class handles the presentation logic for our
 * Shofi_Verticals/List actions's success data.
 *
 * @version         $Id: ListSuccessView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Mvc
 */
class Shofi_Verticals_List_ListSuccessView extends ShofiVerticalsBaseView
{
    /**
     * Handle presentation logic for the web (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Midas - (Orte) Leuchttürme: Liste');

        $this->getLayer('content')->setSlot(
            'list',
            $this->createSlotContainer('Common', 'List', array(
                'config' => $this->getAttribute('config'),
                'state' => $this->getAttribute('state')
            )),
            NULL,
            'read'
        );

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $listState = $this->getAttribute('state');
        $page = round($listState->getOffset() / $listState->getLimit()) + 1;

        $moduleCrumb = array(
            'text' => '(Orte) Leuchttürme',
            'link' => $routing->gen('shofi_verticals.list'),
            'info' => 'Orte - Leuchttürme Listenansicht (Anfang)',
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
                'text' => 'Liste',
                'link' => $routing->gen('shofi_verticals.list', $routeParams),
                'info' => 'Listenansicht (Seite ' . $page . ')',
                'icon' => 'icon-list'
            ));
        }
        else if ($listState->hasSearch())
        {
            $routeParams['search_phrase'] = $listState->getSearch();
            $breadcrumbs[] = array(
                'text' => 'Suche',
                'link' => $routing->gen('shofi_verticals.list', $routeParams),
                'info' => 'Suche nach: ' . $listState->getSearch() . ' (Seite ' . $page . ')',
                'icon' => 'icon-search'
            );
        }
        else if ($listState->hasFilter())
        {
            $routeParams['filter'] = $listState->getFilter();
            $breadcrumbs[] = array(
                'text' => 'Erweiterte Suche',
                'link' => $routing->gen('shofi_verticals.list', $routeParams),
                'info' => 'Erweiterte Suche (Seite ' . $page. ')',
                'icon' => 'icon-search'
            );
        }
        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }
}
