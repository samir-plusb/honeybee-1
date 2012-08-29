<?php

/**
 * The Shofi_Config_ConfigSuccessView class handles the presentation logic for our
 * Shofi/Config actions's success data.
 *
 * @version         $Id: Import_ImperiaSuccessView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Config_ConfigSuccessView extends ShofiBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'ok',
            'messages' => array('Categoryzuweisungen wurden gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array()
        );
        $this->getResponse()->setContent(json_encode($data));
    }

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
        
        $this->setAttribute('_title', 'Midas - Orte: Branchen Matching Tabelle');
        $matcher = $this->getAttribute('matcher');

        $categoryFinder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        $categoryTagEntries = array();
        foreach ($matcher->getMatchMappings() as $extCategory => $categoryIdList)
        {
            $categoryAutocompleteValues = array();
            $cleanCategoryIds = array();
            if (! empty($categoryIdList))
            {
                foreach ($categoryFinder->findByIds($categoryIdList)->getItems() as $category)
                {
                    foreach ($categoryIdList as $idx => $categoryId)
                    {
                        if ($categoryId == $category->getIdentifier())
                        {
                            $categoryAutocompleteValues[$idx] =  array(
                                'label' => $category->getMasterRecord()->getName(), 
                                'value' => $category->getIdentifier()
                            );
                            $cleanCategoryIds[$idx] = $category->getIdentifier();
                            break;
                        }
                    }
                }
                ksort($categoryAutocompleteValues);
                ksort($cleanCategoryIds);
            }
            $categoryTagEntries[$extCategory] = $categoryAutocompleteValues;
            // $macther->setMatchesFor($extCategory, $cleanCategoryIds);
        }
        $this->setAttribute('config_widget_options', array(
            'mappings' => $categoryTagEntries,
            'autocomplete' => $this->getAutoCompleteWidgetOptions()
        ));

        $this->setBreadcrumb();
    }
                
    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();

        $moduleCrumb = array(
            'text' => 'Orte',
            'link' => $routing->gen('shofi.list'),
            'info' => 'Orte Listenansicht',
            'icon' => 'icon-list'
        );

        $breadCrumbs = array(
            array(
                'text' => 'Branchen Matching Tabelle',
                'link' => $routing->gen('shofi.config'),
                'info' => 'Branchen Matching Tabelle',
                'icon' => 'icon-list'
            )
        );

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'midas.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadCrumbs, 'midas.breadcrumbs');
    }

    /**
     * Prepare and return an array that can be used to configure autocomplete
     * for categories on TagsList component instances.
     *
     * @return array
     */
    protected function getAutoCompleteWidgetOptions()
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
            'autobind' => TRUE,
            'autocomplete' => TRUE,
            'autocomplete_uri' => $categoryAutoCompUrl,
            'autocomplete_display_prop' => 'name',
            'autocomplete_value_prop' => 'identifier'
        );
    }
}
