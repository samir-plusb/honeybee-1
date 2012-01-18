<?php

/**
 * The Items_Api_ExtractLocation_Api_ExtractLocationSuccessView class handles Items/Api_ExtractLocation success data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_ExtractLocation_Api_ExtractLocationSuccessView extends ItemsBaseView
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
                    'state' => 'ok',
                    'location' => $this->getAttribute('location')
                )
            )
        );
    }

}

?>