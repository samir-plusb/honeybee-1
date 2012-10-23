<?php

/**
 * The Shofi_Categories_Suggest_SuggestSuccessView class handles the presentation logic for our
 * Shofi_Categories/Suggest actions's success data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 * @subpackage      Mvc
 */
class Shofi_Categories_Suggest_SuggestSuccessView extends ShofiCategoriesBaseView
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
            'state'     => 'ok',
            'messages' => array(),
            'data' => $this->getAttribute('state')->getData()
        );
        
        $this->getResponse()->setContent(htmlspecialchars_decode(json_encode($data)));
    }
}

?>
