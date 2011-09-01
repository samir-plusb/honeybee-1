<?php

class ImperiaDataSourceTest extends AgaviUnitTestCase
{
    const CFG_FIXTURE = 'configs/imports/fixture.polizeimeldungen.datasrc.php';
    
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
    
    public function testNextRecord()
    {
        $record = $this->imperiaDataSource->nextRecord();
    }
    
    protected function loadConfigFixture()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        
        $fixtureFile = $baseDir . self::CFG_FIXTURE;
        
        return include $fixtureFile;
    }
}

?>
