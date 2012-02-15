<?php

class ImportFactoryConfigTest extends AgaviUnitTestCase
{
    const CFG_FIXTURE = 'import/config/config.php';

    protected $imperiaFactoryConfig;

    protected function setUp()
    {
        parent::setUp();

        $this->imperiaFactoryConfig = new ImportFactoryConfig(
            AgaviConfig::get('import.config_dir')
        );
    }

    public function testGetDataImportConfig()
    {
        $fixture = include $this->buildFixturePath();
        $expected = $fixture['dataimports']['couchdb'];

        $importConfig = $this->imperiaFactoryConfig->getDataImportConfig('couchdb');

        $this->assertEquals($expected, $importConfig);
    }

    public function testGetDataSourceConfig()
    {
        $fixture = include $this->buildFixturePath();
        $expected = $fixture['datasources']['imperia'];

        $sourceConfig = $this->imperiaFactoryConfig->getDataSourceConfig('imperia');

        $this->assertEquals($expected, $sourceConfig);
    }

    public function testGetSupportedSettings()
    {
        $expectedSettings = array(
            ImportFactoryConfig::CFG_IMPORTS_CONFIG,
            ImportFactoryConfig::CFG_SOURCES_CONFIG
        );

        $supportedSettings = $this->imperiaFactoryConfig->getSupportSettings();

        foreach ($expectedSettings as $expectedSetting)
        {
            $msg = "Supported settings do not contain expected: " . $expectedSetting;
            $this->assertContains($expectedSetting, $supportedSettings, $msg);
        }
    }

    public function testGetSettings()
    {
        $expectedSettings = array(
            ImportFactoryConfig::CFG_IMPORTS_CONFIG => 'DataImportsFactoryConfig',
            ImportFactoryConfig::CFG_SOURCES_CONFIG => 'DataSourcesFactoryConfig'
        );

        foreach ($expectedSettings as $name => $expectedClass)
        {
            $configObject = $this->imperiaFactoryConfig->getSetting($name);

            $this->assertInstanceOf($expectedClass, $configObject);
        }
    }

    private function buildFixturePath()
    {
        return AgaviConfig::get('core.fixtures_dir') . self::CFG_FIXTURE;
    }

    // @codeCoverageIgnoreEnd
}

?>