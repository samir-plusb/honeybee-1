<?php

class ImportFactoryTest extends AgaviPhpUnitTestCase
{
    const EXPECTED_IMPORT_INSTANCE = 'CouchDbDataImport';

    const EXPECTED_DATASOURCE_INSTANCE = 'ImperiaDataSource';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1389047',
        '/2/10330/10343/10890/1385317',
        '/2/10/65/368/1388875'
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
            ImperiaDataSourceConfig::CFG_DOCIDS => self::$docIds
        );

        $dataSource = $this->factory->createDataSource('imperia', $parameters);

        $this->assertInstanceOf(self::EXPECTED_DATASOURCE_INSTANCE, $dataSource);
    }
}

?>