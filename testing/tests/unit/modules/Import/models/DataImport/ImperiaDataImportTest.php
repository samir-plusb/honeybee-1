<?php

class ImperiaDataImportTest extends AgaviPhpUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';

    const CFG_FIXTURE = 'data/polizeimeldungen.config.php';

    const CFG_DATASRC_FIXTURE = 'data/polizeimeldungen.config.datasource.php';

    const IMPORT_OUTPUTFILE = 'polizeimeldungen.import';

    const EXPECTED_IMPORT_RESULT_HASH = '93dbd0e40764a373e10a1715d137cf8a';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    protected function tearDown()
    {
        unlink($this->buildImportOutputPath());
    }

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testCreateDataImport($factoryConfigFile)
    {
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        $importSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_SETTINGS);
        $importConfig = new ImperiaDataImportConfig($importSettings);
        $import = new ImperiaDataImportMockUp($importConfig);

        $this->assertInstanceOf('ImperiaDataImport', $import);
    }

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testRunDataImport($factoryConfigFile)
    {
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        // Create our datasource.
        $dataSrcSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_DATASRC);
        $dataSrcSettings = array_merge(
            $dataSrcSettings['settings'],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE     => $dataSrcSettings['record'],
                ImperiaDataSourceConfig::CFG_DOCUMENT_IDS    => self::$docIds
            )
        );
        $dataSrcConfig = new ImperiaDataSourceConfig($dataSrcSettings);
        $dataSource = new ImperiaDataSource($dataSrcConfig);

        // Create our importer.
        $importSettings = array_merge(
            $config->getSetting(ImperiaImportFactoryConfig::CFG_SETTINGS),
            array(
                ImperiaDataImportMockUp::SETTING_OUTPUT_FILE => $this->buildImportOutputPath()
            )
        );
        $importConfig = new ImperiaDataImportConfig($importSettings);
        $import = new ImperiaDataImportMockUp($importConfig);

        // And let them rock!
        $success = $import->run($dataSource);

        $this->assertEquals(TRUE, $success);
        $this->assertEquals(self::EXPECTED_IMPORT_RESULT_HASH, $this->calculateImportResultHash());
    }

    public function provideConfigFilePath()
    {
        return array(
            array('factoryConfigFile' => $this->buildConfigFilePath())
        );
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

    private function calculateImportResultHash()
    {
        $contents = file_get_contents($this->buildImportOutputPath());

        return md5($contents);
    }
}

?>
