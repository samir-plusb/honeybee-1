<?php

/**
 * The ItemsModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Setup
 */
class ItemsModuleSetup implements ICouchDatabaseSetup
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our couchdb database.
     */
    const COUCHDB_DATABASE = 'midas_import';
    const DESIGN_DOCID = 'items';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our couchDbClient instance.
     *
     * @var         ExtendedCouchDbClient
     */
    protected $couchDbClient;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        /* @var AgaviDatabase */
        $database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase(self::COUCHDB_DATABASE);
        $this->couchDbClient = $database->getConnection();
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

        $this->createDatabase();
        $this->initViews();
    }

    /**
     * Tear down our current Asset module installation and clean up.
     */
    public function tearDown()
    {
        $this->deleteDatabase();
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Create our couchdb database.
     */
    protected function createDatabase()
    {
        $this->couchDbClient->createDatabase(NULL);
    }

    /**
     * Delete our couchdb database.
     */
    protected function deleteDatabase()
    {
        try
        {
            $this->couchDbClient->deleteDatabase(NULL);
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
    protected function initViews()
    {
        $views = array();
        foreach (glob(__DIR__.'/*.map.js') as $fname)
        {
            $viewName = preg_replace('/.*\/(.*?)\.map\.js$/', '$1', $fname);
            $views[$viewName]['map'] = file_get_contents($fname);
        }
        $doc = array(
            'views' => $views
        );

        $docId = self::DESIGN_DOCID;
        $stat = $this->couchDbClient->getDesignDocument(NULL, $docId);
        if (isset($stat['_rev']))
        {
            $doc['_rev'] = $stat['_rev'];
        }

        $stat = $this->couchDbClient->createDesignDocument(NULL, $docId, $doc);
        if (isset($stat['ok']))
        {
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log('Successfully saved '.$this->getDatabase()->getDatabaseName().'_design/'.$docId, AgaviILogger::INFO);
        }
        else
        {
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__, AgaviILogger::ERROR);
            $__logger->log(print_r($stat,1), AgaviILogger::ERROR);
        }
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