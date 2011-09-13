<?php

/**
 * The ProjectAssetService is a concrete implementation of the IAssetService interface.
 * It exposes a coarse grained crud api for managing assets.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Service
 */
class ProjectAssetService implements IAssetService
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the index of the 'scheme' part of php's parse_url method's result.
     */
    const URI_PART_SCHEME = 'scheme';
    
    /**
     * Holds the index of the 'path' part of php's parse_url method's result.
     */
    const URI_PART_PATH = 'path';
    
    /**
     * Holds the string we use to identify file uri schemes.
     */
    const URI_SCHEME_FILE = 'file';
    
    /**
     * Holds the string we use to identify http uri schemes.
     */
    const URI_SCHEME_HTTP = 'http';
    
    /**
     * Holds the name of our couchdb database.
     */
    const COUCHDB_DATABASE = 'assets';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds the client we use to talk to couchdb.
     * 
     * @var         ExtendedCouchDbClient 
     */
    protected $couchDbClient;
    
    /**
     * Holds the idsequence we use for new assets.
     * 
     * @var         AssetIdSequence 
     */
    protected $idSequence;
    
    /**
     * Holds the idsequence object that delivers new ids for asset creation.
     * 
     * @var         ProjectAssetService 
     */
    protected static $instance;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new ProjectAssetService instance.
     */
    public function __construct()
    {
        $this->couchDbClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
        
        $this->idSequence = new AssetIdSequence();
    }
    
    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Returns a static instance of this class.
     * 
     * @return      ProjectAssetService 
     */
    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ProjectAssetService();
        }
        
        return self::$instance;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <IAssetService IMPL> -----------------------------------
    
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
        $asset = $this->create($assetUri, $metaData);
        
        if ($asset->moveFile($moveOrigin))
        {
            $this->store($asset);
        }
        
        return $asset;
    }
    
    /**
     * Retrieves the corresponding IAssetInfo instance for a given $assetId.
     * 
     * @return      IAssetInfo
     */
    public function get($assetId)
    {
        $asset = $this->couchDbClient->getDoc(self::COUCHDB_DATABASE, $assetId);
        
        return new ProjectAssetInfo($asset['_id'], $asset);
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
            if (!isset($curMetaData[$name]) || $curMetaData[$name] !== $value)
            {
                $isEqual = FALSE;
                break;
            }
        }
        
        if (!$isEqual)
        {
            $assetData[ProjectAssetInfo::XPROP_META_DATA] = $metaData;
            $asset = new ProjectAssetInfo($assetId, $assetData);
            
            return $this->store($asset, $assetData['_rev']);
        }
        
        return FALSE;
    }
    
    /**
     * Deletes the IAssetInfo and it's corresponding binary for th given $assetId
     * 
     * @return      IAssetInfo
     */
    public function delete($assetId)
    {
        $assetData = $this->couchDbClient->getDoc(self::COUCHDB_DATABASE, $assetId);
        $asset = new ProjectAssetInfo($assetData['_id'], $assetData);
        
        if ($this->couchDbClient->deleteDoc(self::COUCHDB_DATABASE, $asset->getId(), $assetData['_rev']))
        {
            return $asset->deleteFile();
        }
        
        return FALSE;
    }
    
    // ---------------------------------- </IAssetService IMPL> ----------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Return a string we can use to connect to couchdb.
     * 
     * @return      string 
     */
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            AgaviConfig::get('couchdb.import.host'),
            AgaviConfig::get('couchdb.import.port')
        );
    }
    
    /**
     * Create a new IAssetInfo from the given uri and meta-data.
     * 
     * @param       string
     * @param       array $metaData
     * 
     * @return      IAssetInfo
     */
    protected function create($assetUri, array $metaData)
    {
        return new ProjectAssetInfo(
            $this->idSequence->nextId(),
            array(
                ProjectAssetInfo::XPROP_ORIGIN    => $assetUri,
                ProjectAssetInfo::XPROP_META_DATA => $metaData
            )
        );
    }
    
    /**
     * Store the given IAssetInfo to the database.
     * If revision is supplied the store call will lead to  an update,
     * otherwise a create will be submitted.
     * 
     * @param       IAssetInfo $asset
     * @param       string $revision
     * 
     * @return      boolean 
     */
    protected function store(IAssetInfo $asset, $revision = NULL)
    {
        $document = $asset->toArray();
        $document['_id'] = $document[ProjectAssetInfo::PROP_ASSET_ID];
        
        if ($revision)
        {
            $document['_rev'] = $revision;
        }
        
        $response = (array)$this->couchDbClient->storeDoc(self::COUCHDB_DATABASE, $document);
        
        if (isset($response['ok']) && TRUE === $response['ok'])
        {
            return TRUE;
        }
        
        return FALSE;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>