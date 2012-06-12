<?php

/**
 * The Shofi_Import_Prototype_Import_PrototypeErrorView class handles the presentation logic for our
 * Shofi/Import_Prototype actions's error data.
 *
 * @version         $Id: Import_ImperiaErrorView.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Import_Prototype_Import_PrototypeErrorView extends ShofiBaseView
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

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "An arror occured while trying to process the provided imperia data:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getErrorMessages());

        $this->getResponse()->setContent($msg);
    }
}

?>