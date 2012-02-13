<?php

/**
 * The News_Api_PrevItem_Api_PrevItemErrorView class handles News/Api_PrevItem's error data presentation.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_PrevItem_Api_PrevItemErrorView extends NewsBaseView
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
                array(
                    'state' => 'error',
                    'msg' => $this->getAttribute('err_message')
                )
            )
        );
    }

}

?>
