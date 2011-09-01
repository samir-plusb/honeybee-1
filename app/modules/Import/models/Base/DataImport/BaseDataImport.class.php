<?php

abstract class BaseDataImport implements IDataImport
{
    private $dataSource;
    
    protected $config;
    
    protected abstract function importRecord(IDataRecord $record);

    public function __construct(IImportConfig $config)
    {
        $this->config = $config;
    }
    
    public function run(IDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        
        $this->init();
        
        while ($record = $dataSource->nextRecord())
        {
            $this->importRecord($record);
        }
        
        $this->cleanup();
        
        $this->dataSource = null;
    }
    
    protected function getDataSource()
    {
        if (null === $this->dataSource)
        {
            throw new DataImportException("The datasource member is only available inside the run method's execution scope.");
        }
        
        return $this->dataSource;
    }
    
    protected function init() {}
    
    protected function cleanup() {}
}

?>
