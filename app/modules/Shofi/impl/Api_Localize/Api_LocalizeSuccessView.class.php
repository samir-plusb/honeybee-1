<?php

/**
 * The News_Api_ExtractLocation_Api_ExtractLocationSuccessView class
 * handles News/Api_ExtractLocation success data presentation.
 *
 * @version         $Id: Api_ExtractLocationSuccessView.class.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class Shofi_Api_Localize_Api_LocalizeSuccessView extends NewsBaseView
{
    /**
     * Handle presentation logic for json api calls.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getResponse()->setContent(
            htmlspecialchars_decode(json_encode(
                array(
                    'state' => 'ok',
                    'location' => $this->getAttribute('location')
                )
            )
        ));
    }
}
