<?php

/**
 * The IDataImportBaseTestCase class provides base functionality for IDataImport implementation tests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Testing
 */
abstract class IDataImportBaseTestCase extends AgaviPhpUnitTestCase
{
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Return the name of an exisitng dataimport configuration.
     * This will be the config used to create the dataimport instance to test.
     *
     * @return      string
     */
    abstract protected function getImportName();

    /**
     * Return the names of exisitng datasource configurations.
     * These will be the configs used to create the datasources run against our tested dataimport.
     *
     * @return      string
     */
    abstract protected function getDataSourceNames();

    /**
     * Return an array of runtime config settings to pass to our dataimport instance's config.
     *
     * @return      array
     */
    abstract protected function getDataSourceParameters($dataSourceName);

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------

    // ---------------------------------- <TESTS> ------------------------------------------------

    /**
     * Test if wee can create an instance of the provided dataimport class.
     */
    public function testCreateDataImport()
    {
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );

        $import = $importFactory->createDataImport($this->getImportName());

        $this->assertInstanceOf('IDataImport', $import);
    }

    /**
     * Test if we can successfully run a given datasource against our dataimport
     * to create new import items.
     *
     * @param       string $dataSourceName
     *
     * @dataProvider provideDataSourceNames
     */
    public function testRunDataImportCreate($dataSourceName)
    {
        $this->runImport($dataSourceName);
    }

    /**
     * Test if we can successfully run a given datasource against our dataimport
     * to update the import items we imported in step one.
     *
     * @param       string $dataSourceName
     *
     * @dataProvider provideDataSourceNames
     *
     * @todo We need an extra update fixture.
     */
    public function testRunDataImportUpdate($dataSourceName)
    {
        $this->runImport($dataSourceName);


    }

    // ---------------------------------- </TESTS> -----------------------------------------------

    /**
     * Convenience method that actually runs the import for a given datasource.
     *
     * @param       string $dataSourceName
     */
    protected function runImport($dataSourceName)
    {
        $importFactory = new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );

        $import = $importFactory->createDataImport($this->getImportName());
        $dataSourceParams = $this->getDataSourceParameters($dataSourceName);
        $dataSource = $importFactory->createDataSource('imperia', $dataSourceParams);
        $import->run($dataSource);
    }

    // @codeCoverageIgnoreStart

    /**
     * Returns an array that is used as a dataprovider to our testImport* methods.
     *
     * @return      array
     */
    public function provideDataSourceNames()
    {
        $arguments = array();

        foreach ($this->getDataSourceNames() as $dataSourceName)
        {
            $arguments[] = array('dataSourceName' => $dataSourceName);
        }

        return $arguments;
    }

    // @codeCoverageIgnoreEnd

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>