<?php

class ProjectAssetService implements IAssetService
{
    const URI_PART_SCHEME = 'scheme';
    
    const URI_PART_PATH = 'path';
    
    const URI_SCHEME_FILE = 'file';
    
    const URI_SCHEME_HTTP = 'http';
    
    const COUCHDB_DATABASE = 'assets';
    
    protected $couchDbClient;
    
    protected $idSequence;
    
    protected static $instance;
    
    public function __construct()
    {
        $this->couchDbClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
        
        $this->idSequence = new AssetIdSequence();
    }
    
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new ProjectAssetService();
        }
        
        return self::$instance;
    }

    /**
     * Store the given file on the filesystem 
     * and returns a IAssetInfo instance that reflects our new asset.
     * 
     * @param       string $assetUri
     * @param       array $metaData
     * 
     * @return      IAssetInfo
     */
    public function put($assetUri, array $metaData = array(), $moveOrigin = TRUE)
    {
        $asset = $this->createAsset($assetUri, $metaData);
        
        if ($asset->moveFile($moveOrigin))
        {
            $this->storeAssetInfo($asset);
        }
        
        return $asset;
    }
    
    /**
     * Update the metadata of the asset with the given $assetId
     *
     * @param       int $assetId
     * @param       array $metaData
     *   
     * @return      IAssetInfo
     */
    public function update($assetId, array $metaData = array())
    {
        $assetData = $this->couchDbClient->getDoc(self::COUCHDB_DATABASE, $assetId);
        $curMetaData = (array)$assetData[ProjectAssetInfo::XPROP_META_DATA];
        $isEqual = TRUE;
        
        foreach ($metaData as $name => $value)
        {
            if (!isset($curMetaData[$name]) || $curMetaData[$name] !== $metaData[$name])
            {
                $isEqual = FALSE;
                break;
            }
        }
        
        if (!$isEqual)
        {
            $assetData[ProjectAssetInfo::XPROP_META_DATA] = $metaData;
            $asset = new ProjectAssetInfo($assetId, $assetData);
            
            return $this->storeAssetInfo($asset, $assetData['_rev']);
        }
        
        return FALSE;
    }
    
    /**
     * Retrieves the corresponding IAssetInfo instance for a given $assetId.
     * 
     * @return      IAssetInfo
     */
    public function get($assetId)
    {
        $asset = $this->couchDbClient->getDoc(self::COUCHDB_DATABASE, $assetId);
        
        return new ProjectAssetInfo($asset['id'], $asset);
    }
    
    /**
     * Deletes the IAssetInfo and it's corresponding binary for th given $assetId
     * 
     * @return      IAssetInfo
     */
    public function delete($assetId)
    {
        $assetData = $this->couchDbClient->getDoc(self::COUCHDB_DATABASE, $assetId);
        $asset = new ProjectAssetInfo($assetData['id'], $assetData);
        
        if ($this->couchDbClient->deleteDoc(self::COUCHDB_DATABASE, $asset->getId(), $assetData['_rev']))
        {
            return $asset->deleteFile();
        }
        
        return FALSE;
    }
    
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            AgaviConfig::get('couchdb.import.host'),
            AgaviConfig::get('couchdb.import.port')
        );
    }
    
    protected function createAsset($assetUri, array $metaData)
    {
        return new ProjectAssetInfo(
            $this->idSequence->nextId(),
            array(
                ProjectAssetInfo::XPROP_ORIGIN    => $assetUri,
                ProjectAssetInfo::XPROP_META_DATA => $metaData
            )
        );
    }
    
    protected function storeAssetInfo(IAssetInfo $asset, $revision = NULL)
    {
        $document = $asset->toArray();
        $document['_id'] = $document['id'];
        
        if ($revision)
        {
            $document['_rev'] = $revision;
        }
        
        $response = (array)$this->couchDbClient->storeDoc(self::COUCHDB_DATABASE, $document);
        
        if (isset($response['ok']) && true === $response['ok'])
        {
            return true;
        }
        
        return false;
    }
}

?>