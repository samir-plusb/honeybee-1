<?php

class ImperiaDataImportTest extends AgaviPhpUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';

    const CFG_FIXTURE = 'data/polizeimeldungen.config.php';

    const CFG_DATASRC_FIXTURE = 'data/polizeimeldungen.config.datasource.php';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testCreateDataImport($factoryConfigFile)
    {
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        $importSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_SETTINGS);
        $importConfig = new ImperiaDataImportConfig($importSettings);
        $import = new ImperiaDataImport($importConfig);

        $this->assertInstanceOf('ImperiaDataImport', $import);
    }

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testRunDataImport($factoryConfigFile)
    {
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        $importSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_SETTINGS);
        $importConfig = new ImperiaDataImportConfig($importSettings);
        $import = new ImperiaDataImport($importConfig);

        $dataSrcSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_DATASRC);
        $dataSrcSettings = array_merge(
            $dataSrcSettings['settings'],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE => $dataSrcSettings['record'],
                ImperiaDataSourceConfig::CFG_DOCUMENT_IDS => self::$docIds
            )
        );
        
        $dataSrcConfig = new ImperiaDataSourceConfig($dataSrcSettings);
        $dataSource = new ImperiaDataSource($dataSrcConfig);
        $success = $import->run($dataSource);

        $this->assertEquals(TRUE, $success);
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
}

?>
