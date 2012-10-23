<?php

/**
 * The Shofi_Categories_Suggest_SuggestErrorView class handles the presentation logic for our
 * Shofi_Categories/Suggest actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 * @subpackage      Mvc
 */
class Shofi_Categories_Suggest_SuggestErrorView extends ShofiCategoriesBaseView
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
            'state'     => 'error',
            'errors' => $this->getAttribute('error_messages'),
            'data' => array()
        );

        $this->getResponse()->setContent(json_encode($data));
    }
}

?>
