<?php

/**
 * The Shofi_Import_PrototypeDelete_Import_PrototypeDeleteSuccessView class handles the presentation logic for our
 * Shofi/Import_PrototypeDelete actions's success data.
 *
 * @version         $Id: Import_ImperiaSuccessView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Import_PrototypeDelete_Import_PrototypeDeleteSuccessView extends ShofiBaseView
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
        $data = array('ok' => TRUE);
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>