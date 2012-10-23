<?php

/**
 * The WorkflowItemsModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Workflow
 * @subpackage      Setup
 */
class ShofiDatabaseSetup extends BaseCouchDatabaseSetup
{
    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->setDatabase(
            AgaviContext::getInstance()->getDatabaseConnection(
                ShofiWorkflowSupervisor::getCouchDbDatabasename()
            )
        );
    }

    /**
     * get the source directory for map and reduce javascript files
     */
    public function getSourceDirectory()
    {
        return __DIR__.'/views';
    }
}

?>