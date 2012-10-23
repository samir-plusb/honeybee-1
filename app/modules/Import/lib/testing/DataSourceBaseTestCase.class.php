<?php

/**
 * The DataSourceBaseTestCase class provides base functionality for IDataSource implementation tests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Testing
 */
abstract class DataSourceBaseTestCase extends AgaviUnitTestCase
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the IComparable::compare() return value for 'records are equal'.
     */
    const RECORDS_ARE_EQUAL = 0;

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the IDataSource implementation, that we are testing.
     *
     * @var         IDataSource
     */
    protected $dataSource;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Return the (class)name of the IDataRecord implementation to test.
     *
     * @return      string
     */
    abstract protected function getDataSourceClass();

    /**
     * Return the (config)name of the concrete datasource to test.
     *
     * @return      string
     */
    abstract protected function getDataSourceName();

    /**
     * Return the max number of iterations expected for your datasource,
     * in respective to the number of records your data fixture provides.
     *
     * @return      int
     */
    abstract protected function getExpectedLoopCount();

    /**
     * Return the (class)name of the IDataRecord type expected to be returned by the tested datasource.
     */
    abstract protected function getExpectedRecordType();

    /**
     * Return a description of the data record to create.
     *
     * @return      string
     */
    abstract protected function getDataSourceDescription();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <AgaviPhpUnitTestCase OVERRIDES> -----------------------

    /**
     * Setup before each test, hence create an instance of the IDataSource
     * implementation, that we are testing.
     *
     * @see         AgaviPhpUnitTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $class = $this->getDataSourceClass();

        $this->dataSource = new $class(
            $this->createDataSourceConfig(),
            $this->getDataSourceName(),
            $this->getDataSourceDescription()
        );
    }

    // ---------------------------------- </AgaviPhpUnitTestCase OVERRIDES> ----------------------


    // ---------------------------------- <TESTS> ------------------------------------------------

    /**
     * Test if all provided records are by the expected type.
     */
    public function testValidRecords()
    {
        while($record = $this->dataSource->nextRecord())
        {
            $this->assertInstanceOf($this->getExpectedRecordType(), $record);
        }
    }

    /**
     * Test if we can iterate over the datasource the expected number of times.
     */
    public function testNextRecordLoop()
    {
        $currentCount = 0;

        while ($this->dataSource->nextRecord())
        {
            $currentCount++;
        }

        $this->assertEquals($this->getExpectedLoopCount(), $currentCount);
    }

    // ---------------------------------- </TESTS> -----------------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Return the DataSourceConfig implementation for the datasource
     * we are testing.
     *
     * @return      DataSourceConfig
     */
    protected function createDataSourceConfig()
    {
        $config = new ImportFactoryConfig(
            AgaviConfig::get('import.config_dir')
        );

        $dataSourceConfig = $config->getDataSourceConfig(
            $this->getDataSourceName()
        );

        $recordType = $dataSourceConfig[DataSourcesFactoryConfig::CFG_RECORD_TYPE];
        $settings = $dataSourceConfig[DataSourcesFactoryConfig::CFG_SETTINGS];
        $configClass = $this->getDataSourceClass() . ImportFactory::CONFIG_CLASS_SUFFIX;

        return new $configClass(
            array_merge(
                $settings,
                $this->getDataSourceParameters(),
                array(
                    DataSourceConfig::CFG_RECORD_TYPE     => $recordType
                )
            )
        );
    }

    /**
     * Return an array of parameters to pass to our datasource's config instance.
     *
     * @return      array
     */
    protected function getDataSourceParameters()
    {
        return array();
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>