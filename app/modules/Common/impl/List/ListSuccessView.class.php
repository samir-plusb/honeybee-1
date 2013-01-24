<?php

/**
 * The Common_List_ListSuccessView class handles Common/List success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_List_ListSuccessView extends CommonBaseView
{
    /**
     * Handle presentation logic for the web (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $listConfig = $parameters->getParameter('config');
        $listState = $parameters->getParameter('state');
        $routing = $this->getContext()->getRouting();
        $searchWidgetOpts = array(
            'search' => $listState->getSearch(),
            'limit' => $listState->getLimit(),
            'sort_field' => $listState->getSortField(),
            'sort_direction' => $listState->getSortDirection(),
            'filter_url' => $routing->gen($listConfig->getRouteName(), array('offset' => 0, 'limit' => $listState->getLimit()))
        );
        $this->setAttribute('is_filtered', $listState->hasFilter());
        if ($listState->hasFilter())
        {
            $this->setAttribute('list_filter', $listState->getFilter());
        }
        $this->setAttribute('search_widget_opts', htmlspecialchars(
            json_encode($searchWidgetOpts)
        ));
        $this->setAttribute(
            'list_base_url',
            $routing->gen($listConfig->getRouteName(), array('offset' => 0, 'limit' => $listState->getLimit()))
        );

        $this->getLayer('content')->setSlot(
            'pagination',
            $this->createSlotContainer('Common', 'Paginate', array('state' => $listState, 'config' => $listConfig))
        );
    }
}

?>
