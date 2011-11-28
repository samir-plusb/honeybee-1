<?php

/**
 * The Items_ListAction is repsonseable for loading our import items for display.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_ListAction extends ItemsBaseAction
{
    const COUCHDB_DATABASE = 'midas_import';

    /**
     * Execute the read logic for this action, hence prompt for an asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $items = array();
        $itemFinder = $this->getContext()->getModel('ItemFinder');

        if ($parameters->hasParameter('search_phrase'))
        {
            $items = $itemFinder->findItemsByText(
                $parameters->getParameter('search_phrase')
            );
        }

        $itemFinder->findItemsById(array('f5c9aad648e7f904c5870ea5cc27ba0c', 'f5c9aad648e7f904c5870ea5cc27c5f2'));
        $this->setAttribute('items', $documents['rows']);

        return 'Success';
    }

    /**
     * Build the uri to use in order to connect to couchdb.
     *
     * @return string
     */
    protected function buildCouchDbUri()
    {
        return sprintf(
                "http://%s:%d/", AgaviConfig::get('couchdb.import.host'), AgaviConfig::get('couchdb.import.port')
        );
    }

}

?>