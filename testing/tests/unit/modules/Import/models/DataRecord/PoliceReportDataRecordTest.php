<?php

class PoliceReportDataRecordTest extends AgaviUnitTestCase
{
    const CFG_XML_FIXTURE = 'data/polizeimeldung.article.xml';

    const CFG_DATA_FIXTURE = 'data/polizeimeldung.article.php';

    protected $policeReportDataRecord;

    protected function setUp()
    {
        parent::setUp();

        $this->policeReportDataRecord = new PoliceReportDataRecord(
            $this->loadXmlArticleFixture()
        );
    }

    /**
     * @param type $expected
     * @param type $fieldname
     *
     * @dataProvider provideExpectedRecordValues
     */
    public function testGetValue($expected, $fieldname)
    {
        $value = $this->policeReportDataRecord->getValue($fieldname);

        $this->assertEquals($expected, $value);
    }

    public function provideExpectedRecordValues()
    {
        $ret = array();

        foreach ($this->loadProcessedArticleDataFixture() as $key => $value)
        {
            $ret[] = array('expected' => $value, 'fieldname' => $key);
        }

        return $ret;
    }

    protected function loadXmlArticleFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_XML_FIXTURE;

        return file_get_contents($fixtureFile);
    }

    protected function loadProcessedArticleDataFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . self::CFG_DATA_FIXTURE;

        return include $fixtureFile;
    }
}

?>
