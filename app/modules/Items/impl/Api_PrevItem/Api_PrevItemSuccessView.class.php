<?php

/**
 * The Items_Api_PrevItem_Api_PrevItemSuccessView class handles Items/Api_PrevItem's success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_PrevItem_Api_PrevItemSuccessView extends ItemsBaseView
{
    /**
     * Handle presentation logic for json requests.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $item = $this->getAttribute('item');
        $itemData = NULL;
        $ticketData = NULL;
        if ($item)
        {
            $itemData = $item->toArray();
            $ticketData = $this->getAttribute('ticket')->toArray();
        }

        $this->getResponse()->setContent(
            json_encode(
                array('state' => 'ok', 'item' => $itemData, 'ticket' => $ticketData)
            )
        );
    }
}

?>
