<?php

/**
 * The Items_FetchNearByItemsAction is repsonseable handling location extraction api requests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_FetchNearByItemsAction extends ItemsBaseAction
{
    /**
     * Execute the read logic for this action, hence extract the data.
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
        $itemFinder = $this->getContext()->getModel('ItemFinder');
        $where = array(
            'dist' => '0.5km',
            'lat' => $parameters->getParameter('lat'),
            'lon' => $parameters->getParameter('lon')
        );

        $result = $itemFinder->nearBy($where, 'publish_date', 'desc', 0, 15);
        $this->setAttribute('items', $result['items']);
        $this->setAttribute('totalCount', $result['totalCount']);

        return 'Success';
    }

    public function handleReadError(AgaviRequestDataHolder $parameters)
    {
        return 'Error';
    }
}

?>