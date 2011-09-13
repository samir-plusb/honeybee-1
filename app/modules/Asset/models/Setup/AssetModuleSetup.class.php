<?php

/**
 * The AssetModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Setup
 */
class AssetModuleSetup
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our couchDbClient instance.
     * 
     * @var         ExtendedCouchCbClient 
     */
    protected $couchDbClient;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new AssetModuleSetup instance.
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
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     * 
     * @param       boolean $tearDownFirst 
     */
    public function setup($tearDownFirst = FALSE)
    {
        if (TRUE === $tearDownFirst)
        {
            $this->tearDown();
        }
        
        $this->createDatabase(ProjectAssetService::COUCHDB_DATABASE);
        $this->initIdSequence(AssetIdSequence::COUCHDB_DATABASE);
    }
    
    /**
     * Tear down our current Asset module installation and clean up.
     */
    public function tearDown()
    {
        $this->deleteDatabase(ProjectAssetService::COUCHDB_DATABASE);
        $this->deleteDatabase(AssetIdSequence::COUCHDB_DATABASE);
    }
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Create our couchdb database.
     */
    protected function createDatabase($database)
    {
        $this->couchDbClient->createDatabase($database);
    }
    
    /**
     * Delete our couchdb database.
     */
    protected function deleteDatabase($database)
    {
        try 
        {
            $this->couchDbClient->deleteDatabase($database);
        }
        catch (CouchDbClientException $e)
        {
            $error = json_decode($e->getMessage(), TRUE);
            
            if (!isset($error['error']) || 'not_found' != $error['error'])
            {
                throw $e;
            }
        }
    }
    
    /**
     * Create a couchdb view used to fetch our current id from our idsequence.
     */
    protected function initIdSequence($database)
    {
        $this->createDatabase($database);
        
        $designDoc = array(
            'views' => array(
                'curId' => array(
                    'map' => 'function(doc)
                    {
                        emit("id", doc);
                    }'
                )
            )
        );
        
        $this->couchDbClient->createDesignDocument($database, 'idsequence', $designDoc);
        
        $firstDoc = array('curId' => 0);
        $this->couchDbClient->storeDocs($database, array($firstDoc));
    }
    
    /**
     * Build a uri that can be used to connect to our couchdb.
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