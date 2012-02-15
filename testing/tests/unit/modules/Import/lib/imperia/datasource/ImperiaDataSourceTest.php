<?php

class ImperiaDataSourceTest extends DataSourceBaseTestCase
{
    const CFG_CONFIG_FIXTURE = 'data/import/imperia/config.datasource.php';

    const CFG_XML_FIXTURE = 'data/import/imperia/polizeimeldung.article1.xml';

    const RECORDS_ARE_EQUAL = 0;

    static private $docIds = array( // normally these are provided by the imperia import trigger
        '/2/10330/10343/10890/1389367',    // without media !!!
        '/2/10330/10343/10890/1385317',
        '/2/10/65/368/1388875'
    );

    protected function getDataSourceClass()
    {
        return 'ImperiaDataSource';
    }

    protected function getDataSourceName()
    {
        return 'imperia';
    }

    protected function getExpectedLoopCount()
    {
        return 3;
    }

    protected function getExpectedRecordType()
    {
        return 'PoliceReportDataRecord';
    }

    protected function getDataSourceParameters()
    {
        return array(
            ImperiaDataSourceConfig::CFG_DOCIDS => self::$docIds
        );
    }

    protected function getDataSourceDescription()
    {
        return 'Provides imperia export xml data.';
    }

    public function testValidRecordData()
    {
        $config = new ImportFactoryConfig(
            AgaviConfig::get('import.config_dir')
        );

        $dataSourceConfig = $config->getDataSourceConfig(
            $this->getDataSourceName()
        );

        $recordType = $dataSourceConfig[DataSourcesFactoryConfig::CFG_RECORD_TYPE];

        $expectedRecord = new $recordType(
            $this->loadRecordFixtureData(),
            new DataRecordConfig(
                array(
                    DataRecordCOnfig::CFG_SOURCE => '',
                    DataRecordConfig::CFG_ORIGIN => 'imperia'
                )
            )
        );

        $record = $this->dataSource->nextRecord();

        $this->assertEquals(self::RECORDS_ARE_EQUAL, $record->compareTo($expectedRecord));
    }

    protected function loadRecordFixtureData()
    {
        $fixtureFile = AgaviConfig::get('core.fixtures_dir') . self::CFG_XML_FIXTURE;

        return file_get_contents($fixtureFile);
    }
}

?>