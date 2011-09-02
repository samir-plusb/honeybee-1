<?php

class ImperiaDataSourceTest extends AgaviUnitTestCase
{
    const CFG_CONFIG_FIXTURE = 'data/polizeimeldungen.config.datasource.php';

    const CFG_XML_FIXTURE = 'data/polizeimeldung.article.xml';

    const RECORDS_ARE_EQUAL = 0;

    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    protected $imperiaDataSource;

    protected function setUp()
    {
        parent::setUp();

        $this->imperiaDataSource = new ImperiaDataSource(
            new ImperiaDataSourceConfig(
                $this->loadDataSourceConfigFixture()
            )
        );
    }

    public function testCreateDataSource()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $factoryConfigFile = $baseDir . 'configs/imports/polizeimeldungen.xml';
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        $dataSource = new ImperiaDataSource(
            $this->createDataSourceConfig()
        );
    }

    public function testNextRecordIsValid()
    {
        $record = $this->imperiaDataSource->nextRecord();

        $expectedRecord = new PoliceReportDataRecord(
            $this->loadPoliceReportXmlFixture()
        );

        $this->assertEquals(self::RECORDS_ARE_EQUAL, $record->compareTo($expectedRecord));
    }

    public function testNextRecordLoop()
    {
        $expectedCount = 3;
        $currentCount = 0;
        $record = null;

        while (($record = $this->imperiaDataSource->nextRecord()))
        {
            $currentCount++;
        }

        $this->assertEquals($expectedCount, $currentCount);
    }

    protected function loadDataSourceConfigFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_CONFIG_FIXTURE;

        return include $fixtureFile;
    }

    protected function loadPoliceReportXmlFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_XML_FIXTURE;

        return file_get_contents($fixtureFile);
    }

    protected function createDataSourceConfig()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $factoryConfigFile = $baseDir . 'configs/imports/polizeimeldungen.xml';
        $config = new ImperiaImportFactoryConfig($factoryConfigFile);

        $dataSrcSettings = $config->getSetting(ImperiaImportFactoryConfig::CFG_DATASRC);

        $dataSrcSettings = array_merge(
            $dataSrcSettings['settings'],
            array(
                ImperiaDataSourceConfig::CFG_RECORD_TYPE => $dataSrcSettings['record'],
                ImperiaDataSourceConfig::CFG_DOCUMENT_IDS => self::$docIds
            )
        );

        return new ImperiaDataSourceConfig($dataSrcSettings);
    }
}

?>
