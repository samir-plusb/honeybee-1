<?php

abstract class DataRecordBaseTestCase extends AgaviPhpUnitTestCase
{
    abstract protected function getRecordXmlFixturePath();

    abstract protected function getRecordResultFixturePath();
    
    abstract protected function getDataRecordClass();
    
    protected $dataRecord;

    protected function setUp()
    {
        parent::setUp();

        $recordImpl = $this->getDataRecordClass();
        
        $this->dataRecord = new $recordImpl(
            $this->loadXmlFixture()
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
        $value = $this->dataRecord->getValue($fieldname);

        $this->assertEquals($expected, $value);
    }
    
    public function testToArray()
    {
        $values = $this->dataRecord->toArray();
        
        foreach ($this->loadProcessedArticleDataFixture() as $key => $value)
        {
            $this->assertArrayHasKey($key, $values);
            $this->assertEquals($value, $values[$key]);
        }
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

    protected function loadXmlFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . $this->getRecordXmlFixturePath();

        return file_get_contents($fixtureFile);
    }

    protected function loadProcessedArticleDataFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        $fixtureFile = $baseDir . $this->getRecordResultFixturePath();

        return include $fixtureFile;
    }
}

?>