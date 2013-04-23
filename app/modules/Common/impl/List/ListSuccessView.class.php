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
        $listRoute = $listConfig->getRouteName();
        $searchWidgetOpts = array(
            'search' => $listState->getSearch(),
            'limit' => $listState->getLimit(),
            'sort_field' => $listState->getSortField(),
            'sort_direction' => $listState->getSortDirection(),
            'search_url' => urldecode($routing->gen($listRoute, array(
                'offset' => 0, 
                'limit' => $listState->getLimit()
            ))),
            'filter_url' => urldecode($routing->gen($listRoute, array(
                'offset' => 0, 
                'limit' => $listState->getLimit()
            )))
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
            $routing->gen($listRoute, array(
                'offset' => 0, 
                'limit' => $listState->getLimit()
            ))
        );

        $this->setAttribute('select_only_mode', $listState->isInSelectOnlyMode());
        $this->setAttribute('has_tree_view', $listConfig->hasTreeView());
        $this->setAttribute('custom_item_actions', $listConfig->getItemActions());

        $modulePrefix = $listConfig->getTypeKey();
        $treeParams = array();
        if ($listState->isInSelectOnlyMode())
        {
            $treeParams = array(
                'referenceField' => $listState->getReferenceField(),
                'referenceModule' => $listState->getReferenceModule()
            );
        }
        $this->setAttribute('tree_view_link', $routing->gen($modulePrefix . '.tree', $treeParams));

        $this->getLayer('content')->setSlot(
            'pagination',
            $this->createSlotContainer('Common', 'Paginate', array('state' => $listState, 'config' => $listConfig))
        );
    }

    /**
     * Handle presentation logic for the web (json).
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        return json_encode($this->getAttribute('list_data'));
    }
}


