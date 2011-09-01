<?php

class ImperiaDataImportTest extends AgaviPhpUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';

    const CFG_FIXTURE = 'data/polizeimeldungen.config.php';

    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testCreateDataImport($factoryConfigFile)
    {
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);
        
        $import = new ImperiaDataImport($config);
        
        $this->assertInstanceOf('ImperiaDataImport', $import);
    }

    public function provideConfigFilePath()
    {
        return array(
            array('expected' => $this->buildConfigFilePath())
        );
    }

    private function buildConfigFilePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FILE_PATH;
    }
}

?>
