<?php

class DataImportFactoryTest extends AgaviPhpUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';

    const EXPECTED_IMPORT_INSTANCE = 'CouchDbDataImport';

    const EXPECTED_DATASOURCE_INSTANCE = 'ImperiaDataSource';
    
    const IMPORT_OUTPUTFILE = 'polizeimeldungen.import';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    protected $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = new DataImportFactory(
            $this->buildConfigFilePath()
        );
    }

    public function testCreateDataImport()
    {
        $importParams = array(
            CouchDbDataImportMockUpConfig::CFG_OUTPUT_FILE => $this->buildImportOutputPath()
        );
        
        $importer = $this->factory->createDataImport($importParams);

        $this->assertInstanceOf(self::EXPECTED_IMPORT_INSTANCE, $importer);
    }

    public function testCreateDataSource()
    {
        $parameters = array(
            ImperiaDataSourceConfig::PARAM_DOCIDS => self::$docIds
        );

        $dataSource = $this->factory->createDataSource($parameters);

        $this->assertInstanceOf(self::EXPECTED_DATASOURCE_INSTANCE, $dataSource);
    }

    private function buildConfigFilePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FILE_PATH;
    }
    
    private function buildImportOutputPath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR;

        return $baseDir . self::IMPORT_OUTPUTFILE;
    }
}

?>