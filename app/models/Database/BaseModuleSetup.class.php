<?php

/**
 * The BaseCouchDatabaseSetup is responseable for setting up our module for usage.
 *
 * @version         $Id: WorkflowModuleSetup.class.php 404 2011-10-19 14:01:50Z tay $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Workflow
 */
abstract class BaseCouchDatabaseSetup implements ICouchDatabaseSetup
{
	/**
	 * our connection to our database
	 *
	 * @var ExtendedCouchDbClient
	 */
 	protected $client;

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

    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * @return ExtendedCouchDbClient
     */
    protected function getDatabase()
    {
        return $this->client;
    }

    /**
     * Create our couchdb database.
     */
    protected function createDatabase()
    {
        $this->getDatabase()->createDatabase(NULL);
    }

    /**
     * Delete our couchdb database.
     */
    protected function deleteDatabase()
    {
        $this->getDatabase()->deleteDatabase(NULL);
    }

    /**
     * Create a couchdb view used to fetch our current id from our idsequence.
     */
    protected function initViews()
    {
        $views = array();
        foreach (glob(__DIR__.'/*.{map,reduce}.js',GLOB_BRACE) as $fname)
        {
        	// match all documents like:
        	//	DesignDoc.Method.map.js
        	//  DesignDoc.Method.reduce.rs
            if (preg_match('#/([^/]+)\.([^/]+)\.(map|reduce)\.js$#', $fname, $m))
            {
            	$views[$m[1]][$m[2]][$m[3]] = file_get_contents($fname);
            }
        }

		foreach ($views as $docid => $methods)
		{
			$doc = array('views' => $methods);
	        $stat = $this->getDatabase()->getDesignDocument(NULL, $docId);
    	    if (isset($stat['_rev']))
        	{
            	$doc['_rev'] = $stat['_rev'];
        	}

	        $stat = $this->getDatabase()->createDesignDocument(NULL, $docid, $doc);
	        if (isset($stat['ok']))
	        {
	            $__logger=AgaviContext::getInstance()->getLoggerManager();
	            $__logger->log('Successfully saved '.$this->getDatabase()->getDatabaseName().'_design/'.$docid, AgaviILogger::INFO);
	        }
	        else
	        {
	            $__logger=AgaviContext::getInstance()->getLoggerManager();
	            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__, AgaviILogger::ERROR);
	            $__logger->log(print_r($stat,1), AgaviILogger::ERROR);
	        }
		}
    }


    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>