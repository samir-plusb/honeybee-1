<?php

/**
 * The WorkflowItemsModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Workflow
 */
class WorkflowModuleSetup implements ICouchDatabaseSetup
{
    /**
     *
     * @var Workflow_SupervisorModel
     */
    protected $supervisor;

    /**
     * Create a new AssetModuleSetup instance.
     */
    public function __construct()
    {
        $this->supervisor = Workflow_SupervisorModel::getInstance();
    }


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
        return $this->supervisor->getCouchClient();
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
        foreach (glob(__DIR__.'/*.map.js') as $fname)
        {
            $viewName = preg_replace('/.*\/(.*?)\.map\.js$/', '$1', $fname);
            $views[$viewName]['map'] = file_get_contents($fname);
        }
        $doc = array(
            'views' => $views
        );

        $docId = 'designWorkflow';
        $stat = $this->getDatabase()->getDesignDocument(NULL, $docId);
        if (isset($stat['_rev']))
        {
            $doc['_rev'] = $stat['_rev'];
        }

        $stat = $this->getDatabase()->createDesignDocument(NULL, $docId, $doc);
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


    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>