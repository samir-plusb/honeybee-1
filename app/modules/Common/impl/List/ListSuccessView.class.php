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

        $referenceModule = $listState->getReferenceModule();
        $referenceField = $listState->getReferenceField();
        $referenceFieldId = $listState->getReferenceFieldId();
        $defaultParams = array(
            'offset' => 0,
            'limit' => $listState->getLimit()
        );

        if ($referenceModule && $referenceField)
        {
            $defaultParams['referenceModule'] = $referenceModule;
            $defaultParams['referenceField'] = $referenceField;
            $defaultParams['referenceFieldId'] = $referenceFieldId;
        }

        $searchWidgetOpts = array(
            'search' => $listState->getSearch(),
            'limit' => $listState->getLimit(),
            'sort_field' => $listState->getSortField(),
            'sort_direction' => $listState->getSortDirection(),
            'search_url' => urldecode($routing->gen($listRoute, $defaultParams)),
            'filter_url' => urldecode($routing->gen($listRoute, $defaultParams))
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
            $routing->gen($listRoute, $defaultParams)
        );

        $this->setAttribute('select_only_mode', $listState->isInSelectOnlyMode());
        $this->setAttribute('has_tree_view', $listConfig->hasTreeView());
        $this->setAttribute('custom_item_actions', $listConfig->getItemActions());

        $modulePrefix = $listConfig->getTypeKey();
        $treeParams = array();
        if ($listState->isInSelectOnlyMode())
        {
            $treeParams = array('referenceField' => $referenceField, 'referenceModule' => $referenceModule);
        }
        $this->setAttribute('tree_view_link', $routing->gen($modulePrefix . '.tree', $treeParams));

        $module = $this->getAttribute('module');
        $createAction = sprintf('%s::create', $module->getOption('prefix'));
        if ($this->getContext()->getUser()->isAllowed($module, $createAction))
        {
            $this->setAttribute('create_link', $routing->gen(
                sprintf('%s.edit', $module->getOption('prefix'))
            ));
        }

        $exportSetting = sprintf('%s.enabled_exports', $module->getOption('prefix'));
        $exportData = array();
        if (($exports = AgaviConfig::get($exportSetting, FALSE)))
        {
            foreach ($exports as $exportName)
            {
                $exportData[$exportName] = $routing->gen($module->getOption('prefix') .'.list', array('limit' => 60000, 'offset' => 0, 'export_format' => $exportName));
            }
        }
        $this->setAttribute('export_data', $exportData);

        $writeAction = sprintf('%s::write', $module->getOption('prefix'));
        $this->setAttribute('readonly', !$this->getContext()->getUser()->isAllowed($module, $writeAction));

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
