<?php

abstract class ImportConfigBaseTestCase extends AgaviPhpUnitTestCase
{
    protected $config;
    
    abstract protected function getConfigFixturePath();

    abstract protected function getConfigImplementor();
    
    protected function setUp()
    {
        parent::setUp();
        
        $configClass = $this->getConfigImplementor();
        
        $this->config = new $configClass(
            $this->loadConfigFixture()
        );
    }

    public final function testGetSupportedSettings()
    {
        $expectedSettings = array_keys($this->loadConfigFixture());
        $supportedSettings = $this->config->getSupportSettings();

        $this->assertEquals($expectedSettings, $supportedSettings);
    }

    /**
	 * @dataProvider provideExpectedSettings
	 */
    public final function testGetSetting($expected, $setting)
    {
        $msg = "The setting " . $setting . " does not match the expected value: " . $expected;
        $setting_val = $this->config->getSetting($setting);

        $this->assertEquals($expected, $setting_val, $msg);
    }

    public final function provideExpectedSettings()
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
        return include $this->getConfigFixturePath();
    }
}

?>