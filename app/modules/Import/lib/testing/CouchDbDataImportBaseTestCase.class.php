<?php

/**
 * The CouchDbDataImportBaseTestCase class provides base functionality for CouchDbDataImport implementation tests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Testing
 */
abstract class CouchDbDataImportBaseTestCase extends AgaviPhpUnitTestCase
{
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------
    
    /**
     * Return the name of an exisitng dataimport configuration.
     * This will be the config used to create the dataimport instance to test.
     * 
     * @return      string
     */
    abstract protected function getImportName();

    /**
     * Return the names of exisitng datasource configurations.
     * These will be the configs used to create the datasources run against our tested dataimport.
     * 
     * @return      string
     */
    abstract protected function getDataSourceNames();

    /**
     * Return an array of runtime config settings to pass to our dataimport instance's config.
     * 
     * @return      array
     */
    abstract protected function getDataSourceParameters($dataSourceName);
    
    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    
    
    // ---------------------------------- <AgaviPhpUnitTestCase OVERRIDES> -----------------------
    // 
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    /**
     * Setup method that is called once before the first test is run.
     * Reset our database to have a clean state for import.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::setupDatabase();
    }
    
    // @codeCoverageIgnoreEnd
    
    // ---------------------------------- </AgaviPhpUnitTestCase OVERRIDES> ----------------------

    
    // ---------------------------------- <TESTS> ------------------------------------------------
    
    /**
     * Test if wee can create an instance of the provided dataimport class.
     */
    public function testCreateDataImport()
    {
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );

        $import = $importFactory->createDataImport($this->getImportName());

        $this->assertInstanceOf('CouchDbDataImport', $import);
    }

    /**
     * Test if we can successfully run a given datasource against our dataimport
     * to create new import items.
     * 
     * @param       string $dataSourceName
     *
     * @dataProvider provideDataSourceNames
     */
    public function testRunDataImportCreate($dataSourceName)
    {
        $this->runImport($dataSourceName);
    }

    /**
     * Test if we can successfully run a given datasource against our dataimport
     * to update the import items we imported in step one.
     * 
     * @param       string $dataSourceName
     *
     * @dataProvider provideDataSourceNames
     * 
     * @todo We need an extra update fixture.
     */
    public function testRunDataImportUpdate($dataSourceName)
    {
        $this->runImport($dataSourceName);
    
        
    }
    
    // ---------------------------------- </TESTS> -----------------------------------------------
        
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    // @codeCoverageIgnoreStart
    
    /**
     * Deletes and recreates our testing database and leaves a green field for our tests.
     * 
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected static function setupDatabase()
    {
        $couchDbHost = AgaviConfig::get('couchdb.import.host');
        $couchDbPort = AgaviConfig::get('couchdb.import.port');
        $couchDbDatabase = AgaviConfig::get('couchdb.import.database');
        $couchUri = sprintf('http://%s:%d/', $couchDbHost, $couchDbPort);

        $coucDbClient = new ExtendedCouchDbClient($couchUri);

        try
        {
            $coucDbClient->createDatabase($couchDbDatabase);
        }
        catch(CouchdbClientException $e)
        {
            $coucDbClient->deleteDatabase($couchDbDatabase);
            $coucDbClient->createDatabase($couchDbDatabase);
        }
    }

    // @codeCoverageIgnoreEnd

    /**
     * Convenience method that actually runs the import for a given datasource.
     * 
     * @param       string $dataSourceName 
     */
    protected function runImport($dataSourceName)
    {
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );

        $import = $importFactory->createDataImport($this->getImportName());

        $dataSourceParams = $this->getDataSourceParameters($dataSourceName);
        $dataSource = $importFactory->createDataSource('imperia', $dataSourceParams);

        // And let them rock!
        $import->run($dataSource);
    }

    // @codeCoverageIgnoreStart
    
    /**
     * Returns an array that is used as a dataprovider to our testImport* methods.
     * 
     * @return      array 
     */
    public function provideDataSourceNames()
    {
        $arguments = array();

        foreach ($this->getDataSourceNames() as $dataSourceName)
        {
            $arguments[] = array('dataSourceName' => $dataSourceName);
        }

        return $arguments;
    }

    // @codeCoverageIgnoreEnd
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>