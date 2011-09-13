<?php

abstract class DataSourceBaseTestCase extends AgaviUnitTestCase
{
    const RECORDS_ARE_EQUAL = 0;

    protected $dataSource;

    abstract protected function getDataSourceClass();
    
    abstract protected function getDataSourceName();
    
    abstract protected function getExpectedLoopCount();
    
    abstract protected function getExpectedRecordType();
    
    protected function setUp()
    {
        parent::setUp();
        
        $class = $this->getDataSourceClass();
        
        $this->dataSource = new $class(
            $this->createDataSourceConfig()
        );
    }

    public function testValidRecords()
    {
        while($record = $this->dataSource->nextRecord())
        {
            $this->assertInstanceOf($this->getExpectedRecordType(), $record);
        }
    }

    public function testNextRecordLoop()
    {
        $currentCount = 0;

        while ($this->dataSource->nextRecord())
        {
            $currentCount++;
        }

        $this->assertEquals($this->getExpectedLoopCount(), $currentCount);
    }

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
    
    protected function getDataSourceParameters()
    {
        return array();
    }
}

?>