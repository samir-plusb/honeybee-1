<?php

abstract class BaseCouchDbImport extends BaseDataImport
{
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
    
    protected function importData(array $data)
    {
        $this->importBuffer[] = $data;
        
        if ($this->importBufferSize === count($this->importBuffer))
        {
            $this->flushImportBuffer();
        }
    }
    
    protected function flushImportBuffer()
    {
        $database = $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_DATABASE);
        $last_result = $this->couchClient->storeDocs($database, $this->importBuffer);
        
        file_put_contents('couch.resp', var_export($last_result, true), FILE_APPEND);
        
        $this->importBuffer = array();
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