<?php

abstract class ImportBaseDataSource implements IDataSource
{
    protected $config;
    
    private $isInitialized = FALSE;

    abstract protected function init();
    
    abstract protected function forwardCursor();
    
    abstract protected function fetchData();
    
    public function __construct(IImportConfig $config)
    {
        $this->config = $config;
    }
    
    public function nextRecord()
    {
        if (!$this->isInitialized)
        {
            $this->init();
            $this->isInitialized = TRUE;
        }
        
        if (!$this->forwardCursor())
        {
            return NULL;
        }
        
        return $this->createRecord(
            $this->fetchData()
        );
    }
    
    protected function createRecord($data)
    {
        $recordClass = $this->config->getSetting(DataSourceConfig::CFG_RECORD_TYPE);

        if (!class_exists($recordClass, true))
        {
            throw new DataSourceException(
                sprintf(
                    "Unable to find provided datarecord class: %s",
                    $recordClass
                )
            );
        }

        $record = new $recordClass($data);

        if (!($record instanceof IDataRecord))
        {
            throw new DataSourceException(
                sprintf(
                    "An invalid IDataRecord implementor was provided. '%s' does not implement the interface IDataRecord.",
                    $recordClass
                )
            );
        }

        return $record;
    }
}

?>