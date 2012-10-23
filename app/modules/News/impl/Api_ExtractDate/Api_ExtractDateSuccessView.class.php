<?php

/**
 * The News_Api_ExtractDate_Api_ExtractDateSuccessView class handles News/Api_ExtractDate success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_ExtractDate_Api_ExtractDateSuccessView extends NewsBaseView
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
            json_encode(
                array('date' => $this->getAttribute('date'))
            )
        );
    }

}

?>