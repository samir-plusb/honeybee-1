<?php

/**
 * The Import_FixturesAction class handles setting up our test fixtures.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class Import_FixturesAction extends ImportBaseAction
{
    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        // Switch database connections to point to our fixture database configs.
        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'EsNewsFixtures',
            'couchdb' => 'CouchWorkflowFixtures'
        ));
        // Tear down the index and the river before resetting the workflow lib.
        $midasIndexSetup = new MidasIndexSetup();
        $midasIndexSetup->tearDown();
        // Reset the couch database for before (news)workflow fixture import.
        $workflowSetup = new WorkflowDatabaseSetup();
        $workflowSetup->setup(TRUE);
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );
        try
        {
            // Enable workflow integration in all cases (environment).
            $dataImport = $importFactory->createDataImport('workflow', array(
                WorkflowItemDataImportConfig::CFG_NOTIFY_SUPERVISOR => TRUE
            ));
            $dataImport->run($importFactory->createDataSource('rss'));
        }
        catch(AgaviAutoloadException $e)
        {
            /* @todo better exception handling */
            $this->setAttribute('errors', array($e->getMessage()));
            return 'Error';
        }
        $midasIndexSetup->setup();
        return 'Success';
    }
}

?>