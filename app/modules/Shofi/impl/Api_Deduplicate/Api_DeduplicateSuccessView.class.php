<?php

/**
 * The Api_DeduplicateSuccessView class handles Shofi/Api_Deduplicate success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class Shofi_Api_Deduplicate_Api_DeduplicateSuccessView extends NewsBaseView
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
                    'state' => 'ok'
                )
            )
        ));
    }
}
