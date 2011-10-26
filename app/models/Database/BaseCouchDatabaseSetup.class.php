<?php

/**
 * The BaseCouchDatabaseSetup is responseable for setting up a couchdb datbase for usage.
 *
 * Subclasses must implement getSourceDirectory()
 *
 * The setup() method is the working horse.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Tom Anheyer
 * @package Database
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
     * @param boolean $tearDownFirst optional drop database first; defaults to FALSE
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

    /**
     * get the source directory for map and reduce javascript files
     *
     * @return string
     */
    abstract public function getSourceDirectory();


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Method reformats javascript functions for use as map/reduce functions in design docs
     *
     * <ul>
     * <li>strip \/* … *\/ comments
     * <li>strip // … comments
     * <li>strip multiple white spaces
     * </ul>
     *
     * @param string $funcString
     * @return string
     */
    protected function reformatJavascript($funcString)
    {
        // strip /* … */ comments
        $funcString = preg_replace('#/\*.*?\*/#s', ' ', $funcString);
        // strip // … comments
        $funcString = preg_replace('#//.*#', ' ', $funcString);
        // strip multiple white spaces
        $funcString = preg_replace('/\s+/s', ' ', $funcString);

        return trim($funcString);
    }


    /**
     * setup couch connection client
     *
     * @param ExtendedCouchDbClient $client
     */
    protected final function setDatabase(ExtendedCouchDbClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return ExtendedCouchDbClient
     */
    protected final function getDatabase()
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
     *
     * This method looks for javascript files in the directory given by
     * {@see getSourceDirectory()}. Each file must contain exact one function.
     * The filename must match the patterns:
     *
     * <ul>
     * <li>DesignDocId.ViewName.map.js
     * <li>DesignDocId.ViewName.reduce.js
     * </ul>
     */
    protected function initViews()
    {
        $views = array();
        $glob = glob($this->getSourceDirectory().'/*.{map,reduce}.js',GLOB_BRACE);
        foreach ($glob as $fname)
        {
            // match all documents like:
            // * DesignDoc.Method.map.js
            // * DesignDoc.Method.reduce.js
            if (preg_match('#/([^/]+)\.([^/]+)\.(map|reduce)\.js$#', $fname, $m))
            {
                $funcString = file_get_contents($fname);
                $views[$m[1]][$m[2]][$m[3]] = $this->reformatJavascript($funcString);
            }
        }

        foreach ($views as $docid => $methods)
        {
            $doc = array('views' => $methods);
            $stat = $this->getDatabase()->getDesignDocument(NULL, $docid);
            if (isset($stat['_rev']))
            {
                $doc['_rev'] = $stat['_rev'];
            }

            $stat = $this->getDatabase()->createDesignDocument(NULL, $docid, $doc);
            if (isset($stat['ok']))
            {
                $__logger=AgaviContext::getInstance()->getLoggerManager();
                $__logger->log(
                    'Successfully saved '.$this->getDatabase()->getDatabaseName().'_design/'.$docid,
                    AgaviILogger::INFO);
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