<?php

/**
 * The WorkflowItemsModuleSetup is responseable for setting up our module for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Workflow
 */
class WorkflowModuleSetup
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
        $this->initItemsView();
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
        $this->getDatabase()->createDatabase(Workflow_SupervisorModel::DATABASE_NAME);
    }

    /**
     * Delete our couchdb database.
     */
    protected function deleteDatabase()
    {
        $this->getDatabase()->deleteDatabase(Workflow_SupervisorModel::DATABASE_NAME);
    }

    /**
     * Create a couchdb view used to fetch our current id from our idsequence.
     */
    protected function initItemsView()
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
        $stat = $this->getDatabase()->getDesignDocument(Workflow_SupervisorModel::DATABASE_NAME, $docId);
        if (isset($stat['_rev']))
        {
            $doc['_rev'] = $stat['_rev'];
        }

        $stat = $this->getDatabase()->createDesignDocument(Workflow_SupervisorModel::DATABASE_NAME, $docId, $doc);
        if (isset($stat['ok']))
        {
            echo 'Successfully saved _design/'.$docId."\n";
        }
        else
        {
            error_log(__METHOD__.":".__LINE__);
            error_log(print_r($stat));
        }
    }


    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>