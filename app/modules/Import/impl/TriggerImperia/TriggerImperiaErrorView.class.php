<?php

/**
 * The Import_TriggerImperia_TriggerImperiaErrorView class handles the presentation logic for our 
 * Import/TriggerImperia actions's error data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerImperia_TriggerImperiaErrorView extends ImportBaseView
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
            'errors' => $this->getValidationErrorMessages()
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
        $msg = "An arror occured while trying to retieve your asset:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getValidationErrorMessages());

        $this->getResponse()->setContent($msg);
    }
}

?>