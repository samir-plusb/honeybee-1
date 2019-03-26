<?php

namespace Honeybee\Agavi\Database\CouchDb;

use Honeybee\Agavi\Database\IDatabaseSetup;

/**
 * The CouchDb\DatabaseSetup is responseable for setting up a couchdb datbase for usage.
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Tom Anheyer
 */
class DatabaseSetup implements IDatabaseSetup
{
    protected $database;

    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param AgaviDatabase $database
     * @param boolean $tearDownFirst optional drop database first; defaults to FALSE
     */
    public function execute(\AgaviDatabase $database, $tearDownFirst = FALSE)
    {
        $this->database = $database;

        if (! ($this->database instanceof Database))
        {
            throw new \AgaviDatabaseException("Only Honeybee\Agavi\Database\CouchDb\Database instances accepted.");
        }

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
        return $this->deleteDatabase();
    }

    /**
     * get the source directory for map and reduce javascript files
     *
     * @return string
     */
    public function getScriptsLocation()
    {
        return $this->database->getParameter('scripts_location');
    }

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
        $funcString = preg_replace('#\s//\s.*#', ' ', $funcString);
        // strip multiple white spaces
        $funcString = preg_replace('/\s+/s', ' ', $funcString);

        return trim($funcString);
    }

    /**
     * @return Honeybee\Agavi\Database\CouchDb\Client
     */
    protected final function getClient()
    {
        return $this->database->getConnection();
    }

    /**
     * Create our couchdb database.
     */
    protected function createDatabase()
    {
        return $this->getClient()->createDatabase(NULL);
    }

    /**
     * Delete our couchdb database.
     */
    protected function deleteDatabase()
    {
        return $this->getClient()->deleteDatabase(NULL);
    }

    /**
     * Create a couchdb view used to fetch our current id from our idsequence.
     *
     * This method looks for javascript files in the directory given by
     * {@see getScriptsLocation()}. Each file must contain exact one function.
     * The filename must match the patterns:
     *
     * <ul>
     * <li>DesignDocId.ViewName.map.js
     * <li>DesignDocId.ViewName.reduce.js
     * </ul>
     */
    protected function initViews()
    {
        $logger_manager = \AgaviContext::getInstance()->getLoggerManager();

        if (!is_dir($this->getScriptsLocation()))
        {
            $logger_manager->logToAll(\AgaviLogger::CRITICAL, get_class($this), "Database view src-directory does not exist " . $this->getScriptsLocation());
            return;
        }

        $glob = glob($this->getScriptsLocation().'/*.{map,reduce,filters}.js', GLOB_BRACE);
        if (!is_array($glob))
        {
            return;
        }

        $docs = array();
        foreach ($glob as $fname)
        {
            // match all documents like:
            // * DesignDoc.Method.map.js
            // * DesignDoc.Method.reduce.js
            // * DesignDoc.Method.filters.js
            if (preg_match('#/([^/]+)\.([^/]+)\.(map|reduce|filters)\.js$#', $fname, $m))
            {
                $funcString = file_get_contents($fname);
                if ('filters' == $m[3])
                {
                    $docs[$m[1]]['filters'][$m[2]] = $this->reformatJavascript($funcString);
                }
                else
                {
                    $docs[$m[1]]['views'][$m[2]][$m[3]] = $this->reformatJavascript($funcString);
                }
            }
        }

        foreach ($docs as $docid => $doc)
        {
            $stat = $this->getClient()->getDesignDocument(NULL, $docid);
            if (isset($stat['_rev']))
            {
                $doc['_rev'] = $stat['_rev'];
            }

            $stat = $this->getClient()->createDesignDocument(NULL, $docid, $doc);
            if (isset($stat['ok']))
            {
                $logger_manager->logInfo('Successfully saved ' . $this->getClient()->getDatabaseName() . " _design/$docid");
            }
            else
            {
                $log_message = sprintf(
                    "[%s]%s::%s:%s:%s\n%s",
                    get_class($this),
                    __METHOD__,
                    __LINE__,
                    __FILE__,
                     print_r($stat, TRUE)
                 );

                $logger_manager->logError($log_message);
            }
        }
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}