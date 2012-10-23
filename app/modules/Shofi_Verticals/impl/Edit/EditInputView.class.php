<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Mvc
 */
class Shofi_Verticals_Edit_EditInputView extends ShofiVerticalsBaseView
{
    /**
     * Run this view for the html output type.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $this->setAttribute('_title', 'Midas - Leuchttürme: Leuchtturm bearbeiten');

        $this->setAttribute('item_data', $this->getAttribute('item')->toArray());
        
        $this->setAttribute(
            'ticket_data', 
            $this->hasAttribute('ticket')
            ? $this->getAttribute('ticket')->toArray()
            : array()
        );

        $widgetData = $this->getWidgets(
            $this->getAttribute('item')
        );

        $this->registerJsWidgetOptions($widgetData['options']);
        $this->registerClientSideController($widgetData['registration']);

        $this->setBreadcrumb();

        $this->setAttribute('category_facets', $this->prepareCategoryStats($this->getAttribute('item')));
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $moduleCrumb = array(
            'text' => 'Orte (Leuchttürme)',
            'link' => $routing->gen('shofi.list'),
            'info' => 'Orte - Leuchttürme Listenansicht (Anfang)',
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
            'text' => 'Leuchtturm bearbeiten',
            'info' => 'Bearbeitung von Leuchtturm: ' . $this->getAttribute('item')->getIdentifier(),
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
    protected function getWidgets(ShofiVerticalsWorkflowItem $workflowItem)
    {
        $routing = $this->getContext()->getRouting();
        $vertical = $workflowItem->getMasterRecord();
        $categoryStore = ShofiCategoriesWorkflowService::getInstance()->getWorkflowSupervisor()->getWorkflowItemStore();
        $categories = array();
        foreach ($vertical->getCategories() as $categoryId)
        {
            $category = $categoryStore->fetchByIdentifier($categoryId);
            if (! $category)
            {
                error_log("Hit non-existing category on place: " . $workflowItem->getIdentifier());
                continue;
            }
            $categories[] = array('label' => $category->getMasterRecord()->getName(), 'value' => $categoryId);
        }

        return array(
            'options' => array(
                'asset_widget_opts' => array(
                    'autobind' => TRUE,
                    'fieldname' => 'vertical[images][]',
                    'max' => 1,
                    'assets' => $this->getPreparedAssetData($vertical->getImages()),
                    'post_url' => $routing->gen('asset.update'),
                    'put_url' => $routing->gen('asset.put'),
                ),
                'top_categories_widget_opts' =>  array_merge(
                    $this->getCategoryAutoCompleteOptions(),
                    array(
                        'autobind' => TRUE,
                        'fieldname' => 'vertical[categories]',
                        'tags' => $categories
                    )
                )
            ),
            'registration' => array(
                array(
                    'name' => 'vertical-images',
                    'selector' => '.widget-verticals-images',
                    'type' => 'AssetList'
                ),
                array(
                    'name' => 'top-categories',
                    'selector' => '.widget-top-categories',
                    'type' => 'TagsList'
                )
            ),
        );
    }

    protected function prepareCategoryStats(ShofiVerticalsWorkflowItem $verticalItem)
    {
        $verticalId = $verticalItem->getIdentifier();
        if (empty($verticalId))
        {
            return array();
        }
        $finder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        return $finder->getCategoryFacetForPlacesByVertical(
            $verticalItem->getIdentifier()
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
            );
        }
        return $assets;
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
}

?>
