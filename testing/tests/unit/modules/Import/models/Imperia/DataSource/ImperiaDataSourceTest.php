<?php

class ImperiaDataSourceTest extends DataSourceBaseTestCase
{
    const CFG_CONFIG_FIXTURE = 'data/import/imperia/config.datasource.php';

    const CFG_XML_FIXTURE = 'data/import/imperia/polizeimeldung.article1.xml';

    const RECORDS_ARE_EQUAL = 0;

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
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
            ImperiaDataSourceConfig::PARAM_DOCIDS => self::$docIds
        );
    }

    protected function getDataSourceDescription()
    {
        return 'Foo the bar had a very fuzen buzen (Imperia).';
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
            'imperia/polizeimeldungen',
            NULL
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