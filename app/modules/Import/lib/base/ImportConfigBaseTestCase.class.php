<?php

/**
 * The ImportConfigBaseTestCase class provides base functionality for IImportConfig implementation tests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class ImportConfigBaseTestCase extends AgaviPhpUnitTestCase
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds the IImportConfig implementation to test.
     * 
     * @var         IImportConfig 
     */
    protected $config;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------
    
    /**
     * Return a file system path pointing to the config fixture to use for testing valid config state.
     * 
     * @return      string
     */
    abstract protected function getConfigFixturePath();
    
    /**
     * Return the class name of the IImportConfig to test.
     * 
     * @return      string
     */
    abstract protected function getConfigImplementor();
    
    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    
    
    // ---------------------------------- <AgaviPhpUnitTestCase OVERRIDES> -----------------------
    
    /**
     * Setup before each test, hence create an instance of the IImportConfig
     * implementation that we are testing.
     * 
     * @see         AgaviPhpUnitTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        
        $configClass = $this->getConfigImplementor();
        
        $this->config = new $configClass(
            $this->loadConfigFixture()
        );
    }
    
    // ---------------------------------- </AgaviPhpUnitTestCase OVERRIDES> ----------------------

    
    // ---------------------------------- <TESTS> ------------------------------------------------
    
    /**
     * Test if the IImportConfig::getSupportedSettings() method is implemented as expected.
     */
    public final function testGetSupportedSettings()
    {
        $expectedSettings = array_keys($this->loadConfigFixture());
        $supportedSettings = $this->config->getSupportSettings();

        $this->assertEquals($expectedSettings, $supportedSettings);
    }

    /**
     * Test if the IImportConfig::getSetting() method is implemented as expected.
     * 
     * @param       mixed $expected
     * @param       string $setting 
     * 
     * @dataProvider provideExpectedSettings
     */
    public final function testGetSetting($expected, $setting)
    {
        $msg = "The setting " . $setting . " does not match the expected value: " . $expected;
        $setting_val = $this->config->getSetting($setting);

        $this->assertEquals($expected, $setting_val, $msg);
    }
    
    // ---------------------------------- </TESTS> -----------------------------------------------

    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * This method serves as the data provider for the testGetSetting test.
     * It returns an array of expected setting name/value pairs.
     * 
     * @return      array 
     */
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
    
    /**
     * Returns a fixture array that is used to validate the state 
     * of the config object that we are testing.
     * 
     * @return      array 
     */
    protected function loadConfigFixture()
    {
        return include $this->getConfigFixturePath();
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>