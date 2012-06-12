<?php

/**
 * The Shofi_Verticals_SuggestErrorView class handles the presentation logic for our
 * Shofi_Verticals/Suggest actions's error data.
 *
 * @version         $Id: SuggestErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Mvc
 */
class Shofi_Verticals_SuggestErrorView extends ShofiVerticalsBaseView
{
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
            'state' => 'error',
            'errors' => $this->getAttribute('error_messages'),
            'data' => array()
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>
