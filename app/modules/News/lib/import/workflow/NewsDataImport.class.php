<?php

/**
 * The NewsDataImport is responseable for importing news data to the domain's workflow.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         News
 * @subpackage      Import/Workflow
 */
class NewsDataImport extends WorkflowItemDataImport
{
    protected function getWorkflowService()
    {
        return NewsWorkflowService::getInstance();
    }


}

?>
