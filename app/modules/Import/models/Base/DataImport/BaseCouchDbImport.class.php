<?php

abstract class BaseCouchDbImport extends BaseDataImport
{
    const COUDB_ID_FIELD = '_id';
    
    const COUDB_REV_FIELD = '_rev';
    
    const COUCHDB_ERR_CONFLICT = 'conflict';
    
    const DEFAULT_BUFFER_SIZE = 50;
    
    protected $couchClient;
    
    protected $importBuffer;
    
    protected $importBufferSize;
    
    public function __construct(IImportConfig $config)
    {
        if (!$config instanceof CouchDbDataImportConfig)
        {
            throw new DataImportException(
                "Invalid config object given. Instance of CouchDbDataImportConfig expected, got: " . get_class($config)
            );
        }
        
        parent::__construct($config);
    }

    /**
     * Setup our couch client and preselect the configured database,
     * so we can start sending data right away.
     */
    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);
        
        $this->couchClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
        
        $this->importBuffer = array();
        $this->importBufferSize = $this->config->getSetting(
            CouchDbDataImportConfig::PARAM_BUFFER_SIZE,
            self::DEFAULT_BUFFER_SIZE
        );
    }
    
    protected function cleanup()
    {
        $this->flushImportBuffer();
        
        parent::cleanup();
    }
    
    protected function convertRecord()
    {
        $data = parent::convertRecord();
        
        $data[self::COUDB_ID_FIELD] = $this->getCurrentRecord()->getIdentifier();
        
        return $data;
    }
    
    protected function importData(array $data)
    {
        $this->importBuffer[$data[self::COUDB_ID_FIELD]] = $data;
        
        if ($this->importBufferSize === count($this->importBuffer))
        {
            $this->flushImportBuffer();
        }
    }
    
    protected function flushImportBuffer()
    {
        $database = $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_DATABASE);
        $couchData = array_values($this->importBuffer);
        
        $result = $this->couchClient->storeDocs($database, $couchData);
        
        $this->resolveConflicts($result);
    }
    
    protected function resolveConflicts(array $resultItems)
    {
        $database = $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_DATABASE);
        $updateData = array();
        
        foreach ($resultItems as $resultItem)
        {
            if (isset($resultItem['error']) && self::COUCHDB_ERR_CONFLICT === $resultItem['error'])
            {
                $rev = $this->couchClient->statDoc($database, $resultItem['id']);
                
                if (0 !== $rev)
                {
                    $newData = $this->importBuffer[$resultItem['id']];
                    $newData[self::COUDB_REV_FIELD] = $rev;
                    $updateData[] = $newData;
                }
            }
        }
        
        if (!empty($updateData))
        {
            /**
             * @todo Handle unresolveable conflicts.
             */
            $this->couchClient->storeDocs($database, $updateData);
        }
    }
    
    /**
     * Build the uri to use in order to connect to couchdb.
     *
     * @return string
     */
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_HOST),
            $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_PORT)
        );
    }
}

?>