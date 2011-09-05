<?php

class ImperiaDataSourceConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'data/polizeimeldungen.config.datasource.php';

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    public function testParseDataSourceConfig()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $factoryConfigFile = $baseDir . 'configs/imports/polizeimeldungen.xml';
        $config = new DataImportFactoryConfig($factoryConfigFile);

        $dataSrcSettings = $config->getSetting(DataImportFactoryConfig::CFG_DATASRC);

        $dataSrcSettings = array_merge(
            $dataSrcSettings['settings'],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE => $dataSrcSettings['record'],
                ImperiaDataSourceConfig::PARAM_DOCIDS => self::$docIds
            )
        );

        $this->assertEquals($this->loadConfigFixture(), $dataSrcSettings);
    }

    protected function getConfigImplementor()
    {
        return 'ImperiaDataSourceConfig';
    }

    protected function getConfigFixturePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FIXTURE;
    }
}

?>
