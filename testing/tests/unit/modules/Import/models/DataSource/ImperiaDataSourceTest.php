<?php

class ImperiaDataSourceTest extends AgaviUnitTestCase
{
    const CFG_CONFIG_FIXTURE = 'data/polizeimeldungen.datasource.php';

    const CFG_XML_FIXTURE = 'data/polizeimeldung.article.xml';

    const RECORDS_ARE_EQUAL = 0;

    protected $imperiaDataSource;

    protected function setUp()
    {
        parent::setUp();

        $this->imperiaDataSource = new ImperiaDataSource(
            new ImperiaDataSourceConfig(
                $this->loadConfigFixture()
            )
        );
    }

    public function testNextRecordIsValid()
    {
        $record = $this->imperiaDataSource->nextRecord();

        $expectedRecord = new ImperiaDataRecord(
            $this->loadXmlArticleFixture()
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

    protected function loadConfigFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_CONFIG_FIXTURE;

        return include $fixtureFile;
    }

    protected function loadXmlArticleFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_XML_FIXTURE;

        return file_get_contents($fixtureFile);
    }
}

?>
