<?php

class ImperiaDataSourceConfigTest extends AgaviUnitTestCase
{
    const CFG_FIXTURE = 'configs/imports/fixture.polizeimeldungen.datasrc.php';
    
    protected $imperiaDataSourceConfig;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->imperiaDataSourceConfig = new ImperiaDataSourceConfig(
            $this->loadConfigFixture()
        );
    }
    
    public function testGetSupportedSettings()
    {
        static $expectedSettings = array(
            'url',
            'account_user',
            'account_pass'
        );
        
        $supportedSettings = $this->imperiaDataSourceConfig->getSupportSettings();
        
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
        $setting_val = $this->imperiaDataSourceConfig->getSetting($setting);
        
        $this->assertEquals($expected, $setting_val, $msg);
    }
    
    public function provideExpectedSettings()
    {
        $fixture = $this->loadConfigFixture();
        $ret = array();
        
        foreach ($fixture as $setting => $value)
        {
            $ret[] = array('expected' => $value, 'setting' => $setting);
        }
        
        return $ret;
    }
    
    protected function loadConfigFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        
        $fixtureFile = $baseDir . self::CFG_FIXTURE;
        
        return include $fixtureFile;
    }
}

?>
