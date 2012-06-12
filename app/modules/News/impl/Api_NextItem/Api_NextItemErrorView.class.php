<?php

/**
 * The News_Api_NextItem_Api_NextItemErrorView class handles News/Api_NextItem's error data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_NextItem_Api_NextItemErrorView extends NewsBaseView
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
