<?php

abstract class CouchDbDataImportBaseTestCase extends AgaviPhpUnitTestCase
{
    abstract protected function getImportName();
    
    abstract protected function getDataSourceNames();
    
    abstract protected function getDataSourceParameters($dataSourceName);
    
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::setupDatabase();
    }
    
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
     *
     * @param type $dataSourceName 
     * 
     * @dataProvider provideDataSourceNames
     */
    public function testRunDataImportCreate($dataSourceName)
    {
        $this->runImport($dataSourceName);
    }
    
    /**
     *
     * @param type $dataSourceName 
     * 
     * @dataProvider provideDataSourceNames
     */
    public function testRunDataImportUpdate($dataSourceName)
    {
        $this->runImport($dataSourceName);
    }
    
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
}

?>