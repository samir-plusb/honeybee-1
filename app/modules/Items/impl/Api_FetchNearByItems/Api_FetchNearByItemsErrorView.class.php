<?php

/**
 * The Items_FetchNearByItems_FetchNearByItemsSuccessView class
 * handles Items/FetchNearByItems success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_FetchNearByItems_FetchNearByItemsErrorView extends ItemsBaseView
{
    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getResponse()->setContent(
            json_encode(
                array(
                    'state' => 'error',
                    'msg' => 'Didnt work...'
                )
            )
        );
    }

}

?>