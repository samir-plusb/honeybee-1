<?php

class ImperiaImportFactoryConfigTest extends AgaviUnitTestCase
{
    const CFG_FILE_PATH = 'configs/imports/polizeimeldungen.xml';
    
    const CFG_FIXTURE = 'configs/imports/fixture.polizeimeldungen.php';
    
    protected $imperiaFactoryConfig;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->imperiaFactoryConfig = new ImperiaImportFactoryConfig(
            $this->buildConfigFilePath()
        );
    }
    
    /**
	 * @dataProvider provideConfigFilePath
	 */
    public function testUriParsing($expected)
    {
        $uriParts = $this->imperiaFactoryConfig->getUriParts();
        
        $this->assertEquals($expected, $uriParts['path']);
        $this->assertEquals($expected, $this->imperiaFactoryConfig->getUri());
    }
    
    public function testGetSupportedSettings()
    {
        static $expectedSettings = array(
            'class',
            'name',
            'description',
            'settings',
            'datasource'
        );
        
        $supportedSettings = $this->imperiaFactoryConfig->getSupportSettings();
        
        foreach ($expectedSettings as $expectedSetting)
        {
            $msg = "Supported settings do not contain expected: " . $expectedSetting;
            $this->assertContains($expectedSetting, $supportedSettings, $msg);
        }
    }
    
    /**
	 * @dataProvider provideExpectedSettings
	 */
    public function testGetSetting($expected, $setting)
    {
        $msg = "The setting " . $setting . " does not match the expected value: " . $expected;
        $setting_val = $this->imperiaFactoryConfig->getSetting($setting);
        
        $this->assertEquals($expected, $setting_val, $msg);
    }
    
    public function provideConfigFilePath()
    {
        return array(
            array('expected' => $this->buildConfigFilePath())
        );
    }
    
    public function provideExpectedSettings()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        
        $fixtureFile = $baseDir . self::CFG_FIXTURE;
        
        $fixture = include $fixtureFile;
        $ret = array();
        
        foreach ($fixture['import'] as $setting => $value)
        {
            $ret[] = array('expected' => $value, 'setting' => $setting);
        }
        
        return $ret;
    }
    
    private function buildConfigFilePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        
        return $baseDir . self::CFG_FILE_PATH;
    }
}

?>
