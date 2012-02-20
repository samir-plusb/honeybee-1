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
        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'EsNewsFixtures',
            'couchdb' => 'CouchWorkflowFixtures'
        ));
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );
        $dataImport = $importFactory->createDataImport('workflow');
        $dataSource = $importFactory->createDataSource('rss');
        try
        {
            $dataImport->run($dataSource);
        }
        catch(AgaviAutoloadException $e)
        {
            /* @todo better exception handling */
            $this->setAttribute('errors', array($e->getMessage()));
            return 'Error';
        }
        return 'Success';
    }
}

?>