<?php

/**
 * The DataRecordBaseTestCase class provides base functionality for IDataRecord implementation tests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Testing
 */
abstract class DataRecordBaseTestCase extends AgaviPhpUnitTestCase
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the IDataRecord implementation to test.
     *
     * @var         IDataRecord
     */
    protected $dataRecord;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Return a file system path pointing to a xml fixture that we will parse for testing.
     */
    abstract protected function getRecordXmlFixturePath();

    /**
     * Return a file system path pointing to the config fixture to use for testing valid data-record state.
     */
    abstract protected function getRecordResultFixturePath();

    /**
     * Return the class name of the IDataRecord to test.
     */
    abstract protected function getDataRecordClass();

    /**
     * Return the name of the origin to pass to our data record.
     *
     * @return      string
     */
    abstract protected function getDataRecordOrigin();
    
    /**
     * Return the name of the source to pass to our data record.
     *
     * @return      string
     */
    abstract protected function getDataRecordSource();

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

        // Reset our asset module so we can guess ids (start at 1).
        $setup = new AssetModuleSetup();
        $setup->setup(TRUE);

        $recordImpl = $this->getDataRecordClass();

        $this->dataRecord = new $recordImpl(
            $this->loadXmlFixture(),
            new DataRecordConfig(
                array(
                    DataRecordConfig::CFG_ORIGIN => $this->getDataRecordOrigin(),
                    DataRecordConfig::CFG_SOURCE => $this->getDataRecordSource()
                )
            )
        );
    }

    // ---------------------------------- </AgaviPhpUnitTestCase OVERRIDES> ----------------------


    // ---------------------------------- <TESTS> ------------------------------------------------

    /**
     * Test if the IDataRecord::getValue() method is implemented as expected.
     *
     * @param       mixed $expected
     * @param       string $setting
     *
     * @dataProvider provideExpectedGetterParams
     */
    public function testInterfaceGetter($expected, $getterName)
    {
        $isCallable = is_callable(array($this->dataRecord, $getterName));

        $this->assertEquals(TRUE, $isCallable, "The given interface method: '$getterName' is not callable!");
        $this->assertEquals($expected, $this->dataRecord->$getterName());
    }

    /**
     * Test if the IDataRecord::toArray() method is implemented as expected.
     */
    public function testToArray()
    {
        $values = $this->dataRecord->toArray();

        foreach ($this->loadDataRecordResultFixture() as $key => $value)
        {
            $this->assertArrayHasKey($key, $values);
            $this->assertEquals($value, $values[$key]);
        }
    }

    // ---------------------------------- </TESTS> -----------------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    /**
     * This method serves as the data provider for the testGetValue test.
     * It returns an array of expected setting name/value pairs.
     *
     * @return      array
     */
    public function provideExpectedGetterParams()
    {
        $ret = array();

        foreach ($this->loadDataRecordResultFixture() as $propName => $value)
        {
            $getterMethod = 'get' . ucfirst($propName);

            $ret[] = array('expected' => $value, 'getterName' => $getterMethod);
        }

        return $ret;
    }

    // @codeCoverageIgnoreEnd

    /**
     * Returns a xml fixture that is passed to the record,
     * that we are currently testing before we assert our expectations.
     *
     * @return      string
     */
    protected function loadXmlFixture()
    {
        $fixtureFile = AgaviConfig::get('core.fixtures_dir') . $this->getRecordXmlFixturePath();

        return file_get_contents($fixtureFile);
    }

    /**
     * Returns a fixture array that is used to validate the state
     * of the IDataRecord instance that we are currently testing.
     *
     * @return      array
     */
    protected function loadDataRecordResultFixture()
    {
        $fixtureFile = AgaviConfig::get('core.fixtures_dir') . $this->getRecordResultFixturePath();

        return include $fixtureFile;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>