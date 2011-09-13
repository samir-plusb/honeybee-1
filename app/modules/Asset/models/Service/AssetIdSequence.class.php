<?php

class AssetIdSequence
{
    const COUCHDB_DATABASE = 'asset_idsequence';
    
    protected $couchDbClient;
    
    public function __construct()
    {
        $this->couchDbClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
    }
    
    public function nextId()
    {
        $viewData = $this->couchDbClient->getView(self::COUCHDB_DATABASE, 'idsequence', 'curId');
            
        if (1 !== $viewData['total_rows'])
        {
            throw new Exception(
                "The idsequence is an invalid state as it has more than row for the curId view."
            );
        }

        $row = $viewData['rows'][0];
        $docData = $row['value'];
        $docData['curId']++;
        
        $this->couchDbClient->storeDoc(self::COUCHDB_DATABASE, $docData);
        
        return $docData['curId'];
    }
    
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            AgaviConfig::get('couchdb.import.host'),
            AgaviConfig::get('couchdb.import.port')
        );
    }
}

?>