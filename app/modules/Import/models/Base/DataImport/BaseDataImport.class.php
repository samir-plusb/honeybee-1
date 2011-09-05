<?php

abstract class BaseDataImport implements IDataImport
{
    private $dataSource;
    
    private $currentRecord;

    protected $config;

    protected abstract function importData(array $data);

    public function __construct(IImportConfig $config)
    {
        $this->config = $config;
    }

    public function run(IDataSource $dataSource)
    {
        $this->init($dataSource);

        while ($this->currentRecord = $dataSource->nextRecord())
        {
            if (!$this->processRecord())
            {
                // @todo Need to think of a smart error handling,
                // as the overall import process is not allowed to be affected by single record related errors.
            }
        }

        $this->cleanup();
        
        return true;
    }
    
    protected function processRecord()
    {
        $data = $this->convertRecord();
        
        return $this->importData($data);
    }
    
    protected function convertRecord()
    {
        return $this->getCurrentRecord()->toArray();
    }

    protected function getDataSource()
    {
        if (null === $this->dataSource)
        {
            throw new DataImportException(
                "The dataSource member is only available inside the run method's execution scope."
            );
        }

        return $this->dataSource;
    }
    
    protected function getCurrentRecord()
    {
        if (null === $this->currentRecord)
        {
            throw new DataImportException(
                "The currentRecord member is only available inside the run method's execution scope."
            );
        }

        return $this->currentRecord;
    }

    protected function init(IDataSource $dataSource) 
    {
        $this->dataSource = $dataSource;
    }

    protected function cleanup() 
    {
        $this->dataSource = null;
        $this->currentRecord = null;
    }
}

?>