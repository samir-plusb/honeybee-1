<?php

abstract class ImportBaseDataSource implements IDataSource
{
    protected $config;
    
    private $isInitialized = FALSE;

    abstract protected function init();
    
    abstract protected function forwardCursor();
    
    abstract protected function createRecord();
    
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
        
        return $this->createRecord();
    }
}

?>
