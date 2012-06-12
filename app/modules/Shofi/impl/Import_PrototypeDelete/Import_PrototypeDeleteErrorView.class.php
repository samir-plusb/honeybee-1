<?php

/**
 * The Shofi_Import_PrototypeDelete_Import_PrototypeDeleteErrorView class handles the presentation logic for our
 * Shofi/Import_PrototypeDelete actions's error data.
 *
 * @version         $Id: Import_ImperiaErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Import_PrototypeDelete_Import_PrototypeDeleteErrorView extends ShofiBaseView
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
            'ok'     => FALSE,
            'errors' => $this->getErrorMessages()
        );

        $this->getResponse()->setContent(json_encode($data));
    }
}

?>