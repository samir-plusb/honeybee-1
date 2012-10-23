<?php

/**
 * The News_FetchNearByItemsAction is repsonseable handling location extraction api requests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_FetchNearByItemsAction extends NewsBaseAction
{
    /**
     * Execute the read logic for this action, hence find the news items near the provided content-item.
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
        $itemFinder = $this->getContext()->getModel('NewsFinder');
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

    /**
     * Handle read errors, hence failed validation on the incoming parameters.
     *
     * @param AgaviRequestDataHolder $parameters
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleReadError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return 'Error';
    }
}

?>