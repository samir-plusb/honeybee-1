<?php

/**
 * The Common_ListAction is repsonseable for rendering list data in a reusable way :).
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_ListAction extends CommonBaseAction
{
    const PATH_DATA_PREFIX = 'data';
    
    /**
     * Execute the read logic for this action, hence load our news items.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $routing = $this->getContext()->getRouting();
        $listConfig = $parameters->getParameter('config');
        $listState = $parameters->getParameter('state');

        $this->setAttribute('list_fields', $listConfig->getFields());
        $this->setAttribute('item_actions',$listConfig->getItemActions());
        $this->setAttribute('batch_actions',$listConfig->getBatchActions());
        $this->setAttribute('list_data', $this->renderListValues($listConfig, $listState));
        $this->setAttribute('templates', $this->renderListTemplates($listConfig, $listState));

        $this->setAttribute('total_count', $listState->getTotalCount());
        $this->setAttribute('offset', $listState->getOffset());
        $this->setAttribute('limit', $listState->getLimit());
        $this->setAttribute('module_type_key', $listConfig->getTypeKey());
        $clientSideOptions = $listConfig->getClientSideController();
        $clientSideOptions['options'] = isset($clientSideOptions['options']) ? $clientSideOptions['options'] : array();
        $clientSideOptions['options']['workflow_urls'] = array(
            'checkout' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.checkout', $listConfig->getTypeKey()))
            )),
            'release' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.release', $listConfig->getTypeKey()))
            )),
            'execute' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.execute', $listConfig->getTypeKey()))
            )),
            'edit' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.edit', $listConfig->getTypeKey()))
            ))
        );
        $clientSideOptions['options']['select_only_mode'] = $listState->isInSelectOnlyMode();
        $clientSideOptions['options']['reference_field'] = $listState->getReferenceField();
        $this->setAttribute('client_side_controller', $clientSideOptions);
        $this->setAttribute('list_route', $listConfig->getRouteName());
        $this->setAttribute('translation_domain', $listConfig->getTranslationDomain());
        $this->setAttribute('sorting', array(
            'direction' => $listState->getSortDirection(),
            'field'     => $listState->getSortField()
        ));
        if ($listState->hasSearch())
        {
            $this->setAttribute('search', $listState->getSearch());
        }

        return 'Success';
    }

    protected function renderListTemplates(IListConfig $listConfig, IListState $listState)
    {
        $templates = array();
        $rendererPool = array();
        foreach ($listConfig->getFields() as $fieldname => $field)
        {
            $rendererClass = $field->getRenderer();
            $renderer = NULL;
            if (! isset($rendererPool[$rendererClass]))
            {
                $renderer = new $rendererClass($this->getModule());
                $rendererPool[$rendererClass] = $renderer;
            }
            else
            {
                $renderer = $rendererPool[$rendererClass];
            }
            $templates[$fieldname] = $renderer->renderTemplate($field);
        }
        return $templates;
    }

    protected function renderListValues(IListConfig $listConfig, IListState $listState)
    {
        $listData = array();
        $rendererPool = array();
        foreach ($listState->getData() as $row)
        {
            $renderedData = array();
            foreach ($listConfig->getFields() as $fieldname => $field)
            {
                $arrayPath = new AgaviVirtualArrayPath(
                    sprintf(
                        '%s[%s]',
                        self::PATH_DATA_PREFIX,
                        implode('][', explode('.', $field->getValuefield()))
                    )
                );
                $value = $arrayPath->getValue($row);
                $rendererClass = $field->getRenderer();
                $renderer = NULL;
                if ($field->hasRenderer())
                {
                    if (! isset($rendererPool[$rendererClass]))
                    {
                        $renderer = new $rendererClass($this->getModule());
                        $rendererPool[$rendererClass] = $renderer;
                    }
                    else
                    {
                        $renderer = $rendererPool[$rendererClass];
                    }
                    $renderedData[$fieldname] = $renderer->renderValue($value, $field, $row);
                }
                else
                {
                    $renderedData[$fieldname] = $value;
                }
            }
            $row['display_data'] = $renderedData;
            $row['css_classes'] = isset($row['css_classes']) ? $row['css_classes'] : array();
            $listData[] = $row;
        }
        return array(
            'listItems' => $listData,
            'metaData' => array(
                'search' => $listState->getSearch(),
                'has_filter' => $listState->hasFilter(),
                'item_count' => $listState->getTotalCount()
            )
        );
    }
}
