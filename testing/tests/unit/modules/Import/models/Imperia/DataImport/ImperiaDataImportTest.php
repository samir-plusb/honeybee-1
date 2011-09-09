<?php

class ImperiaDataImportTest extends AgaviPhpUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';

    const CFG_FIXTURE = 'data/import/imperia/polizeimeldungen.config.php';

    const CFG_DATASRC_FIXTURE = 'data/import/imperia/polizeimeldungen.config.datasource.php';

    const IMPORT_OUTPUTFILE = 'polizeimeldungen.import';

    const EXPECTED_IMPORT_RESULT_HASH = 'b4522809eeca2678ac604e6d43a918b8';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

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

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testCreateDataImport($factoryConfigFile)
    {
        $config = new DataImportFactoryConfig($factoryConfigFile);

        $importSettings = $config->getSetting(DataImportFactoryConfig::CFG_SETTINGS);
        $importConfig = new ImperiaDataImportConfig($importSettings);
        $import = new ImperiaDataImportMockUp($importConfig);

        $this->assertInstanceOf('ImperiaDataImport', $import);
    }

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testRunDataImportCreate($factoryConfigFile)
    {
        $importFactory = new DataImportFactory($factoryConfigFile);

        $importParams = array(
            ImperiaDataImportMockUp::SETTING_OUTPUT_FILE => $this->buildImportOutputPath()
        );
        $import = $importFactory->createDataImport('ImperiaDataImportConfig', $importParams);

        $dataSourceParams = array(
            ImperiaDataSourceConfig::PARAM_DOCIDS => self::$docIds
        );
        $dataSource = $importFactory->createDataSource('ImperiaDataSourceConfig', $dataSourceParams);

        // And let them rock!
        $success = $import->run($dataSource);

        $this->assertEquals(TRUE, $success);
        $this->assertEquals(self::EXPECTED_IMPORT_RESULT_HASH, $this->calculateImportResultHash());
    }
    
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function provideConfigFilePath()
    {
        return array(
            array('factoryConfigFile' => $this->buildConfigFilePath()),
            array('factoryConfigFile' => $this->buildConfigFilePath())
        );
    }
    
    // @codeCoverageIgnoreEnd

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

    private function calculateImportResultHash()
    {
        $contents = file_get_contents($this->buildImportOutputPath());

        return md5($contents);
    }
}

?>