<?php

/**
 * The News_Import_Imperia_Import_ImperiaErrorView class handles the presentation logic for our
 * News/Import_Imperia actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Import_Imperia_Import_ImperiaErrorView extends NewsBaseView
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