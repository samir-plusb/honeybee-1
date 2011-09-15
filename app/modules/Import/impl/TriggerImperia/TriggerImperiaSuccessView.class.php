<?php

/**
 * The Import_TriggerImperia_TriggerImperiaSuccessView class handles the presentation logic for our 
 * Import/TriggerImperia actions's success data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerImperia_TriggerImperiaSuccessView extends ImportBaseView
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
        $msg = "Successfully imported your imperia data" . PHP_EOL;

        $this->getResponse()->setContent($msg);
    }
}

?>