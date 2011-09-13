<?php

/**
 * The AssetIdSequence class provides an incremental sequence of id's that are unique
 * for the scope of it's current sequence.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Service
 */
class AssetIdSequence
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our coucdb database.
     */
    const COUCHDB_DATABASE = 'asset_idsequence';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds the client we use to talk to couchdb.
     * 
     * @var         ExtendedCouchDbClient 
     */
    protected $couchDbClient;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new AssetIdSequence instance,
     * thereby initializing our couchdb client.
     */
    public function __construct()
    {
        $this->couchDbClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );
    }
    
    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Return the next id from our sequence,
     * thereby incrementing the current one.
     * 
     * @return      int 
     */
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
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
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
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>