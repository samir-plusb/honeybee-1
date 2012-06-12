<?php

/**
 * The Shofi_Verticals_List_ListErrorView class handles the presentation logic for our
 * Shofi_Verticals/List actions's error data.
 *
 * @version         $Id: ListErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Mvc
 */
class Shofi_Verticals_List_ListErrorView extends ShofiVerticalsBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
    }

    /**
     * Handle presentation logic for json.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $data = array(
            'ok'     => FALSE,
            'errors' => $this->getErrorMessages()
        );

        $this->getResponse()->setContent(json_encode($data));
    }
}

?>
