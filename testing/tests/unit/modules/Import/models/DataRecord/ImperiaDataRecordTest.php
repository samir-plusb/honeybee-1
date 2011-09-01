<?php

class ImperiaDataRecordTest extends AgaviUnitTestCase
{
    const CFG_FIXTURE = 'configs/imports/fixture.polizeimeldungen.datarec.xml';
    
    protected $imperiaDataRecord;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->imperiaDataRecord = new ImperiaDataRecord(
            $this->loadConfigFixture()
        );
    }
    
    public function testGetValue()
    {
        $value = $this->imperiaDataRecord->getValue('foobar');
        
        
    }
    
    protected function loadConfigFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        
        $fixtureFile = $baseDir . self::CFG_FIXTURE;
        
        return file_get_contents($fixtureFile);
    }
}

?>
