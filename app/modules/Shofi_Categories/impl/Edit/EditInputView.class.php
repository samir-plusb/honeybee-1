<?php

class Shofi_Categories_Edit_EditInputView extends ShofiCategoriesBaseView
{
    /**
     * Run this view for the html output type.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $this->setAttribute('_title', 'Shofi Categories - Edit');
        $categoryItem = $this->getAttribute('item');
        $this->setAttribute('item_data', $categoryItem->toArray());

        $this->setAttribute(
            'ticket_data', 
            $this->hasAttribute('ticket')
            ? $this->getAttribute('ticket')->toArray()
            : array()
        );

        $itemId = $categoryItem->getIdentifier();
        if (! empty($itemId))
        {
            $this->setAttribute('contentmachine_link', sprintf(
                'cm://Shofi/Category?id=%1$s',
                $itemId
            ));
        }

        $this->setAttribute('verticals', $this->prepareVerticals());
        $this->setAttribute('places_data', $this->getPlacesForCategory($categoryItem));
        $widgetData = $this->getWidgets($categoryItem);

        $this->registerJsWidgetOptions($widgetData['options']);
        $this->registerClientSideController($widgetData['registration']);

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $moduleCrumb = array(
            'text' => 'Orte (Kategorien)',
            'link' => $routing->gen('shofi.list'),
            'info' => 'Orte - Kategorien Listenansicht (Anfang)',
            'icon' => 'icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'midas.breadcrumbs', array());
        foreach ($breadcrumbs as $crumb)
        {
            if ('icon-pencil' === $crumb['icon'])
            {
                return;
            }
        }
        $breadcrumbs[] = array(
            'text' => 'Kategorie bearbeiten',
            'info' => 'Bearbeitung von Kategorie: ' . $this->getAttribute('item')->getIdentifier(),
            'icon' => 'icon-pencil'
        );
        
        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }

    /**
     * Register the given widgets to the client side controller.
     */
    protected function registerClientSideController(array $widgets = array())
    {
        $controllerOptions = array(
            'autobind' => TRUE,
            'widgets' => $widgets
        );
        $this->setAttribute(
            'controller_options',
            htmlspecialchars(json_encode($controllerOptions))
        );
    }

    protected function registerJsWidgetOptions(array $widgets = array())
    {
        foreach ($widgets as $attributeName => $widgetOptions)
        {
            $this->setAttribute(
                $attributeName,
                htmlspecialchars(json_encode($widgetOptions))
            );
        }
    }

    /**
     * register widgets by providing: name, type and selector
     * init widgets by providing options below a key you will use in your templates.
     */
    protected function getWidgets(ShofiCategoriesWorkflowItem $workflowItem)
    {
        $routing = $this->getContext()->getRouting();
        $category = $workflowItem->getMasterRecord();
        $tags = array();
        foreach ($category->getTags() as $tag)
        {
            $tags[] = array('label' => $tag, 'value' => $tag);
        }
        $keywords = array();
        foreach ($category->getKeywords() as $keyword)
        {
            $keywords[] = array('label' => $keyword, 'value' => $keyword);
        }
        $widgetOptions = array( // template-attributes for passing options to particular widgets
            'tags_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'category[tags]',
                'tags' => $tags
            ),
            'keywords_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'category[keywords]',
                'tags' => $keywords
            ),
            'vertical_dropdown_opts' => array(
                'autobind' => TRUE
            ),
            'asset_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'category[attachments][]',
                'post_url' => $routing->gen('asset.update'),
                'put_url' => $routing->gen('asset.put'),
                'max' => 1,
                'assets' => $this->getPreparedAssetData(
                    $workflowItem->getMasterRecord()->getAttachments()
                )
            ),
            'clipboard_widget_opts' => array(
                'autobind' => TRUE,
                'copy_trigger_el' => '.clipboard-widget-trigger',
                'copy_text' => $this->getAttribute('contentmachine_link')
            )
        );
        $widgetRegistration = array( // register widgets to client-side controller
            array(
                'name' => 'tags',
                'type' => 'TagsList',
                'selector' => '.widget-category-tags'
            ),
            array(
                'name' => 'tags',
                'type' => 'TagsList',
                'selector' => '.widget-category-keywords'
            ),
            array(
                'name' => 'verticals-dropdown',
                'type' => 'DropdownWidget',
                'selector' => '.widget-verticals-dropdown'
            ),
            array(
                'name' => 'category-attachments',
                'type' => 'AssetList',
                'selector' => '.widget-category-attachments'
            ),
            array(
                'name' => 'cm-url2clipboard',
                'type' => 'ClipboardWidget',
                'selector' => '.clipboard-widget'
            )
        );
        return array(
            'options' => $widgetOptions,
            'registration' => $widgetRegistration
        );
    }

    protected function prepareVerticals()
    {
        $verticals = array();
        $verticalsFinder = ShofiVerticalsFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_verticals.list_config')
        ));
        foreach ($verticalsFinder->find(ListState::fromArray(array(
            'limit' => 100,
            'sortField' => 'created',
            'sortDirection' => 'desc'
        )))->getItems() as $vertical)
        {
            $verticals[] = array(
                'id' => $vertical->getIdentifier(),
                'name' => $vertical->getMasterRecord()->getName()
            );
        }
        return $verticals;
    }

    protected function getPlacesForCategory(ShofiCategoriesWorkflowItem $categoryItem)
    {
        $categoryId = $categoryItem->getIdentifier();
        if (empty($categoryId))
        {
            return array('items' => array(), 'total_count' => 0);
        }
        $placesFinder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));

        $result = $placesFinder->find(ListState::fromArray(array(
            'limit' => 1000,
            'sortField' => 'name',
            'sortDirection' => 'desc',
            'filter' => array(
                'detailItem.category' => $categoryId
            )
        )));
        $placesData = array('items' => array(), 'total_count' => $result->getTotalCount());
        foreach ($result->getItems() as $placeItem)
        {
            $placesData['items'][] = array(
                'name' => $placeItem->getCoreItem()->getName(),
                'ticket' => $placeItem->getTicketId()
            );
        }
        return $placesData;
    }

    protected function getPreparedAssetData(array $assetIds)
    {
        $routing = $this->getContext()->getRouting();
        $assets = array();
        foreach (ProjectAssetService::getInstance()->multiGet($assetIds) as $id => $asset)
        {
            $metaData = $asset->getMetaData();
            $assets[] = array(
                'id' => $id,
                'url' => $routing->gen('asset.binary', array('aid' => $id)),
                'name' => $asset->getFullName(),
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
            );
        }
        return $assets;
    }
}

?>