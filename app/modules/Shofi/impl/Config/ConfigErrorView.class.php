<?php

/**
 * The Shofi_Config_ConfigErrorView class handles the presentation logic for our
 * Shofi/Config actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Config_ConfigErrorView extends ShofiBaseView
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
