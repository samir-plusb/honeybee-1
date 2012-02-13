<?php

/**
 * The Workflow_Setup_SetupErrorView class handle the presentation logic for our Workflow/Setup actions's error data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Workflow
 * @subpackage      Mvc
 */
class Workflow_Setup_SetupErrorView extends AssetBaseView
{
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
        $errors = implode(PHP_EOL, $this->getAttribute('errors', array()));
        $this->getResponse()->setContent(
            "An error occured while trying to setup your Workflow module: " . PHP_EOL . $errors
        );
    }

}

?>