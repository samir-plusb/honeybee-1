<?php

class ImportFactoryTest extends AgaviPhpUnitTestCase
{
    const EXPECTED_IMPORT_INSTANCE = 'CouchDbDataImport';

    const EXPECTED_DATASOURCE_INSTANCE = 'ImperiaDataSource';
    
    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        
        $this->factory = new ImportFactory(
            new ImportFactoryConfig(AgaviConfig::get('import.config_dir'))
        );
    }

    public function testCreateDataImport()
    {
        $importer = $this->factory->createDataImport('couchdb');

        $this->assertInstanceOf(self::EXPECTED_IMPORT_INSTANCE, $importer);
    }

    public function testCreateDataSource()
    {
        $parameters = array(
            ImperiaDataSourceConfig::PARAM_DOCIDS => self::$docIds
        );

        $dataSource = $this->factory->createDataSource('imperia', $parameters);

        $this->assertInstanceOf(self::EXPECTED_DATASOURCE_INSTANCE, $dataSource);
    }
}

?>