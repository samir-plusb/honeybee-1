<?php

class Shofi_Edit_EditInputView extends ShofiBaseView
{
    const PAGE_CORE = 'CoreItem';

    const PAGE_DETAIL = 'DetailItem';

    const PAGE_SALES = 'SalesItem';

    /**
     * Run this view for the html output type.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Midas - Orte: Ort bearbeiten');

        $this->setAttribute('item_data', $this->getAttribute('item')->toArray());
        $itemId = $this->getAttribute('item')->getIdentifier();
        if (! empty($itemId))
        {
            $this->setAttribute('contentmachine_link', sprintf(
                'cm://Shofi/Place?id=%1$s',
                $itemId
            ));
        }

        $this->setAttribute(
            'ticket_data', 
            $this->hasAttribute('ticket')
            ? $this->getAttribute('ticket')->toArray()
            : array()
        );

        $page = $parameters->getParameter('_page', self::PAGE_CORE);
        $this->setAttribute('page', $page);

        $tpl = $this->getTemplateFor($page);
        $this->getLayer('content')->setTemplate($tpl);

        $widgets = $this->getWidgetsFor($page, $this->getAttribute('item'));
        $this->registerJsWidgetOptions($widgets['options']);
        $this->registerClientSideController($widgets['registration']);

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $moduleCrumb = array(
            'text' => 'Orte',
            'link' => $routing->gen('shofi.list'),
            'info' => 'Alle Orte die in Midas verwaltet werden.',
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
            'text' => 'Ort bearbeiten',
            'info' => 'Bearbeitung von Ort: ' . $this->getAttribute('item')->getIdentifier(),
            'icon' => 'icon-pencil'
        );

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'midas.breadcrumbs');
    }

    /**
     * Get the particular template name for the given edit page.
     */
    protected function getTemplateFor($page)
    {
        return 'Edit/'.$page;
    }

    /**
     * Return the the widgets to use on a particular edit page.
     */
    protected function getWidgetsFor($page, ShofiWorkflowItem $shofiItem)
    {
        $widgets = array(
            'options' => array(),
            'registration' => array()
        );
        if (self::PAGE_DETAIL === $page)
        {
            $widgets = $this->getDetailWidgets($shofiItem);
        }
        else if (self::PAGE_SALES === $page)
        {
            $widgets = $this->getSalesWidgets($shofiItem);
        }
        else
        {
            $widgets = $this->getCoreWidgets($shofiItem);
        }
        return $widgets;
    }

    /**
     * Generate and add(attributes) the options for the widgets on the given page.
     * These options will directly be passed to the corresponding widget instances.
     */
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

    protected function getCoreWidgets(ShofiWorkflowItem $shofiItem)
    {
        $routing = $this->getContext()->getRouting();
        $coreItem = $shofiItem->getCoreItem();
        $location = $coreItem->getLocation();
        $widgetOptions = array( // template-attributes for passing options to particular widgets
            'location_widget_opts' => array(
                'autobind' => TRUE,
                'localize_url' => urldecode(htmlspecialchars($routing->gen('news.api.extract_location', array('geo_text' => '{STRING}')))),
                'location' => $location->toArray()
            ),
            'clipboard_widget_opts' => array(
                'autobind' => TRUE,
                'copy_trigger_el' => '.clipboard-widget-trigger',
                'copy_text' => $this->getAttribute('contentmachine_link')
            )
        );
        $widgetRegistration = array( // register widgets to client-side controller
            array(
                'name' => 'location',
                'type' => 'LocationWidget',
                'selector' => '.widget-location-widget'
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

    /**
     * Register javascript copmonent options for the detail page.
     */
    protected function getDetailWidgets(ShofiWorkflowItem $shofiItem)
    {
        $routing = $this->getContext()->getRouting();
        $detailItem = $shofiItem->getDetailItem();
        $categoryAutoCompleteOpts = $this->getCategoryAutoCompleteOptions();
        $keywords = array();
        foreach ($detailItem->getKeywords() as $keyword)
        {
            $keywords[] = array('label' => $keyword, 'value' => $keyword);
        }
        $internalKeywords = array();
        foreach ($detailItem->getInternalKeywords() as $internalKeyword)
        {
            $internalKeywords[] = array('label' => $internalKeyword, 'value' => $internalKeyword);
        }
        $categoryStore = ShofiCategoriesWorkflowService::getInstance()->getWorkflowSupervisor()->getWorkflowItemStore();
        $additionalCategories = array();
        foreach ($detailItem->getAdditionalCategories() as $categoryId)
        {
            $category = $categoryStore->fetchByIdentifier($categoryId);
            if (! $category)
            {
                error_log("Hit non-existing category on place: " . $shofiItem->getIdentifier());
                continue;
            }
            $additionalCategories[] = array('label' => $category->getMasterRecord()->getName(), 'value' => $categoryId);
        }
        $categoryOpts = array();
        if ($detailItem->getCategory())
        {
            $category = $categoryStore->fetchByIdentifier($detailItem->getCategory());
            if ($category)
            {
                $categoryOpts = array(
                    array('label' => $category->getMasterRecord()->getName(), 'value' => $category->getIdentifier())
                );
            }
            else
            {
                 error_log("Hit non-existing category on place: " . $shofiItem->getIdentifier());
            }
        }

        $widgetOptions = array( // template-attributes for passing options to particular widgets
            'keywords_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[keywords]',
                'tags' => $keywords
            ),
            'internal_keywords_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[internalKeywords]',
                'tags' => $internalKeywords
            ),
            'opening_times_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[openingTimes]',
                'data' => $detailItem->getOpeningTimes()
            ),
            'attributes_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[attributes]',
                'data' => $detailItem->getAttributes()
            ),
            'additional_categories_widget_opts' => array_merge(
                $categoryAutoCompleteOpts,
                array(
                    'autobind' => TRUE,
                    'fieldname' => 'detailItem[additionalCategories]',
                    'tags' => $additionalCategories
                )
            ),
            'category_widget_opts' => array_merge(
                $categoryAutoCompleteOpts,
                array(
                    'autobind' => TRUE,
                    'fieldname' => 'detailItem[category]',
                    'max' => 1,
                    'tags' => $categoryOpts
                )
            ),
            'asset_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[attachments][]',
                'post_url' => $routing->gen('asset.update'),
                'put_url' => $routing->gen('asset.put'),
                'assets' => $this->getPreparedAssetData(
                    $shofiItem->getDetailItem()->getAttachments()
                )
            ),
            'clipboard_widget_opts' => array(
                'autobind' => TRUE,
                'copy_trigger_el' => '.clipboard-widget-trigger',
                'copy_text' => $this->getAttribute('contentmachine_link')
            ),
            'embed_code_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'detailItem[videoEmbedCode]',
                'embed_code' => $shofiItem->getDetailItem()->getVideoEmbedCode()
            )
        );

        $widgetRegistration = array( // register widgets to client-side controller
            array(
                'name' => 'keywords',
                'type' => 'TagsList',
                'selector' => '.widget-keywords'
            ),
            array(
                'name' => 'internal-keywords',
                'type' => 'TagsList',
                'selector' => '.widget-internal-keywords'
            ),
            array(
                'name' => 'opening-times',
                'type' => 'TimeTable',
                'selector' => '.widget-opening-times'
            ),
            array(
                'name' => 'attributes',
                'type' => 'KeyValuesList',
                'selector' => '.widget-attributes'
            ),
            array(
                'name' => 'additional-categories',
                'type' => 'TagsList',
                'selector' => '.widget-additional-categories'
            ),
            array(
                'name' => 'category',
                'type' => 'TagsList',
                'selector' => '.widget-category'
            ),
            array(
                'name' => 'detail-attachments',
                'type' => 'AssetList',
                'selector' => '.widget-detail-attachments'
            ),
            array(
                'name' => 'cm-url2clipboard',
                'type' => 'ClipboardWidget',
                'selector' => '.clipboard-widget'
            ),
            array(
                'name' => 'video-embed-code',
                'type' => 'EmbedCodeWidget',
                'selector' => '.video-embed-code-widget'
            )
        );

        return array(
            'options' => $widgetOptions,
            'registration' => $widgetRegistration
        );
    }

    /**
     * Register javascript copmonent options for the sales page.
     */
    protected function getSalesWidgets(ShofiWorkflowItem $shofiItem)
    {
        $routing = $this->getContext()->getRouting();
        $salesItem = $shofiItem->getSalesItem();
        $keywords = array();
        foreach ($salesItem->getKeywords() as $keyword)
        {
            $keywords[] = array('label' => $keyword, 'value' => $keyword);
        }
        $categoryStore = ShofiCategoriesWorkflowService::getInstance()->getWorkflowSupervisor()->getWorkflowItemStore();
        $additionalCategories = array();
        foreach ($salesItem->getAdditionalCategories() as $categoryId)
        {
            $category = $categoryStore->fetchByIdentifier($categoryId);
            if (! $category)
            {
                error_log("Hit non exisitng category-id(". $categoryId .") for place-item: " . $shofiItem->getIdentifier());
                continue;
            }
            $additionalCategories[] = array(
                'label' => $category->getMasterRecord()->getName(),
                'value' => $categoryId
            );
        }

        $widgetOptions = array( // template-attributes for passing options to particular widgets
            'keywords_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'salesItem[keywords]',
                'tags' => $keywords
            ),
            'additional_categories_widget_opts' => array_merge(
                $this->getCategoryAutoCompleteOptions(),
                array(
                    'autobind' => TRUE,
                    'fieldname' => 'salesItem[additionalCategories]',
                    'tags' => $additionalCategories
                )
            ),
            'asset_widget_opts' => array(
                'autobind' => TRUE,
                'fieldname' => 'salesItem[attachments][]',
                'post_url' => $routing->gen('asset.update'),
                'put_url' => $routing->gen('asset.put'),
                'assets' => $this->getPreparedAssetData(
                    $shofiItem->getSalesItem()->getAttachments()
                ),
            ),
            'clipboard_widget_opts' => array(
                'autobind' => TRUE,
                'copy_trigger_el' => '.clipboard-widget-trigger',
                'copy_text' => $this->getAttribute('contentmachine_link')
            )
        );
        $widgetRegistration = array( // register widgets to client-side controller
            array(
                'name' => 'keywords',
                'type' => 'TagsList',
                'selector' => '.widget-keywords'
            ),
            array(
                'name' => 'additional-categories',
                'type' => 'TagsList',
                'selector' => '.widget-additional-categories'
            ),
            array(
                'name' => 'sales-attachments',
                'type' => 'AssetList',
                'selector' => '.widget-sales-attachments'
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

    /**
     * Prepare and return an array that can be used to configure autocomplete
     * for categories on TagsList component instances.
     *
     * @return array
     */
    protected function getCategoryAutoCompleteOptions()
    {
        $routing = $this->getContext()->getRouting();

        // first expand agavi config directive to support setting references.
        $categoryAutoCompUrl = AgaviToolkit::expandDirectives(
            AgaviConfig::get('shofi.category_autocomp_uri')
        );

        // then check if we are dealing with an absolut url,
        // else try to generate and application link with agavi
        $categoryAutoCompUrl = (FALSE === strpos($categoryAutoCompUrl, 'http')) ? urldecode(htmlspecialchars($routing->gen(
            $categoryAutoCompUrl,
            array('search_phrase' => '{PHRASE}')
        ))) : $categoryAutoCompUrl;

        return array(
            'autocomplete' => TRUE,
            'autocomplete_uri' => $categoryAutoCompUrl,
            'autocomplete_display_prop' => 'name',
            'autocomplete_value_prop' => 'identifier'
        );
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
                'aoi' => isset($metaData['aoi']) ? $metaData['aoi'] : NULL,
            );
        }
        return $assets;
    }
}

?>
