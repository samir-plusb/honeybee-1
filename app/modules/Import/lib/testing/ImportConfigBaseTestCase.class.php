<?php

/**
 * The ImportConfigBaseTestCase class provides base functionality for IConfig implementation tests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Testing
 */
abstract class ImportConfigBaseTestCase extends AgaviPhpUnitTestCase
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the IConfig implementation to test.
     *
     * @var         IConfig
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
     * Return the class name of the IConfig to test.
     *
     * @return      string
     */
    abstract protected function getConfigImplementor();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <AgaviPhpUnitTestCase OVERRIDES> -----------------------

    /**
     * Setup before each test, hence create an instance of the IConfig
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
     * Test if the IConfig::getSupportedSettings() method is implemented as expected.
     */
    public final function testGetSupportedSettings()
    {
        $expectedSettings = array_keys($this->loadConfigFixture());
        $supportedSettings = $this->config->getSupportSettings();

        $this->assertEquals($expectedSettings, $supportedSettings);
    }

    /**
     * Test if the IConfig::getSetting() method is implemented as expected.
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

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

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

    // @codeCoverageIgnoreEnd

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