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
        $couchClient = $this->getContext()->getDatabaseConnection('midas_import');
        $documents = $couchClient->getView(self::COUCHDB_DATABASE, 'items', 'list', NULL, 100);

        $items = array();

        if (isset($documents['rows']) && is_array($documents['rows']))
        {
            foreach ($documents['rows'] as $row)
            {
                $items[] = $couchClient->getDoc(self::COUCHDB_DATABASE, $row['id']);
            }
        }

        $this->setAttribute('items', $items);

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