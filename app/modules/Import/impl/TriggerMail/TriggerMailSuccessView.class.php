<?php

/**
 * The Import_TriggerMail_TriggerMailSuccessView class handle the presentation logic for our Import/TriggerMail actions's success data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_TriggerMail_TriggerMailSuccessView extends ImportBaseView
{
    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $this->getResponse()->setContent(
            "Successfully imported your procmail mime-mail." . PHP_EOL
        );
    }
}

?>