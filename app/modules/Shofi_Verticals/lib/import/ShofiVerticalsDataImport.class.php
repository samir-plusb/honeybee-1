<?php

/**
 * The ShofiVerticalsDataImport is responseable for importing shofi verticals to the domain's workflow.
 *
 * @version         $Id: ShofiVerticalsDataImport.class.php 1154 2012-05-09 10:59:17Z tschmitt $
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
