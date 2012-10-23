<?php

/**
 * The ShofiVerticalsDataImport is responseable for importing shofi verticals to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi_Verticals
 * @subpackage      Import
 */
class ShofiVerticalsDataImport extends WorkflowItemDataImport
{
    protected function getWorkflowService()
    {
        return ShofiVerticalsWorkflowService::getInstance();
    }
}

?>
