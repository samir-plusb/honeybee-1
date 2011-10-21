<?php
/**
 * The Workflow supervisor
 * * aims as factory for workflow handlers and tickets
 * * acts as interface to the UI
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */

class Workflow_SupervisorModel extends ProjectWorkflowBaseModel
{
    /**
     * database config name
     */
    const DATABASE_CONFIG_NAME = 'CouchWorkflow';

    /**
     * database name
     */
    const DATABASE_NAME = 'workflow';

    /**
     *
     * name of couchdb design document to use
     */
    const DESIGNDOC = 'designWorkflow';

    /**
     * ticket database name
     */
    const DATABASE_TICKETS = 'tickets';

    /**
     * path relative to app directory to workflow xml definitions
     */
    const WORKFLOW_CONFIG_DIR = 'modules/Workflow/config/workflows/';

    /**
     * our couchclient instance
     *
     * @var ExtendedCouchDbClient
     */
    private $couchClient;

    /**
     * list of allready loaded workflows
     *
     * @var array
     */
    private $workflowByName = array();

    /**
     * @return Workflow_SupervisorModel
     */
    static function getInstance()
    {
        $context = AgaviContext::getInstance();

        $model = $context->getRequest()->getAttribute(__CLASS__);
        if (! $model instanceof Workflow_SupervisorModel)
        {
            $model = $context->getModel('Supervisor', 'Workflow');
            $context->getRequest()->setAttribute(__CLASS__, $model);
        }
        return $model;
    }


    /**
     * (non-PHPdoc)
     * @see AgaviModel::initialize()
     */
    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);
        $database = $this->context->getDatabaseManager()->getDatabase(self::DATABASE_CONFIG_NAME);
        $this->couchClient = $database->getConnection();
    }


    /**
     * get couchdb client handle instance from agavi database manager
     *
     * @return ExtendedCouchDbClient
     */
    public function getCouchClient()
    {
        return $this->couchClient;
    }


    /**
     * callback method is called after a successfully import an item
     *
     * @param IEvent $event expected is a BaseDataImport::EVENT_RECORD_SUCCESS event
     *                      with a data member named 'record' of type IDataRecord
     * @return void
     */
    public function importRecordImportedCallback(IEvent $event)
    {
        if ($event->getName() !== BaseDataImport::EVENT_RECORD_SUCCESS)
        {
            return;
        }

        $data = $event->getData();
        if (! isset($data['record']) || ! $data['record'] instanceof IDataRecord)
        {
            return;
        }

        $ticket = $this->getTicketByImportitem($data['record']);
        $workflow = $this->getWorkflowByName($ticket->getWorkflow());
        $workflow->run($ticket);
    }

    /**
     * find a workflow ticket using its correpondenting import item
     *
     * This method gets registered in {@see ImportBaseAction::initialize()}
     *
     * @todo move method getTicketByImportitem to a ticket handler class
     *
     * @param IDataRecord $record
     * @return WorkflowTicket
     */
    public function getTicketByImportitem(IDataRecord $record)
    {
        $result = $this->getCouchClient()->getView(
            NULL, self::DESIGNDOC, "ticketByImportitem",
            json_encode($record->getIdentifier()),
            0,
            array('include_docs' => 'true')
        );

        if (empty($result['rows']))
        {
            return $this->createNewTicketFromImportItem($record);
        }

        $data = $result['rows'][0]['doc'];
        return new WorkflowTicket($data, $record);
    }


    /**
     * create a ticket for a newly imported item
     *
     * @todo move method createNewTicketFromImportItem to a ticket handler class
     *
     * @param IDataRecord $record
     * @return WorkflowTicket
     */
    public function createNewTicketFromImportItem(IDataRecord $record)
    {
        $ticket = new WorkflowTicket();
        $ticket->setImportItem($record);
        $ticket->setWorkflow('_init');
        $this->saveTicket($ticket);
        return $ticket;
    }

    /**
     *
     *
     * @param WorkflowTicket $ticket
     * @return boolean
     */
    public function saveTicket(WorkflowTicket $ticket)
    {
        $document = $ticket->toArray();
        $result = $this->couchClient->storeDoc(NULL, $document);
        return TRUE;
    }

    /**
     *
     *
     * @param string $identifier
     */
    public function getTicketById($identifier)
    {
        $db = $this->getCouchClient();
        $data = $db->getDoc(self::DATABASE_TICKETS, $identifier);
        $ticket = new WorkflowTicket($data);
        return $ticket;
    }

    /**
     * load a saved import item for use in tickets
     *
     * @see WorkflowTicket::fromArray()
     * @todo implement Workflow_SupervisorModel::getImportItem
     *
     * @param string $identifier
     * @return IDataRecord
     */
    public function getImportItem($identifier)
    {
        return NULL;
    }

    /**
     * get a new WorkflowHandler instance for a named workflow
     *
     * Workflows are defined by XML files under directory {@see WORKFLOW_CONFIG_DIR}
     *
     * @throws WorkflowExceptionon unreadable workflow configuration, etc.
     * @param string $name name of workflow
     * @return WorkflowHandler
     */
    public function getWorkflowByName($name)
    {
        $name = strtolower($name);
        if (! preg_match('/^_?[a-z][a-z-0-9]+$/', $name))
        {
            throw new WorkflowException(
               'Workflow name contains invalid characters: '.$name,
                WorkflowException::INVALID_WORKFLOW_NAME);
        }
        if (! array_key_exists($name, $this->workflowByName))
        {
            $configPath = self::WORKFLOW_CONFIG_DIR . $name . '.workflow.xml';
            try
            {
                $config = include AgaviConfigCache::checkConfig($configPath);
            }
            catch (AgaviUnreadableException $e)
            {
                throw new WorkflowException($e->getMessage(), WorkflowException::WORKFLOW_NOT_FOUND, $e);
            }

            if (! array_key_exists('workflow', $config))
            {
                throw new WorkflowException(
                    'Workflow definition structure is invalid.',
                    WorkflowException::INVALID_WORKFLOW);
            }
            $this->workflowByName[$name] = new WorkflowHandler($config['workflow']);
        }
        return $this->workflowByName[$name];
    }


    /**
     * find and initialize a plugin by its name
     *
     * @param string $pluginName name of plugin
     * @return IWorkflowPlugin
     * @throws WorkflowException on class not found errors or initialize problems
     */
    public function getPluginByName($pluginName)
    {
        $className = 'Workflow'.$pluginName.'Plugin';
        if (! class_exists($className, TRUE))
        {
            throw new WorkflowException("Can not find class '$class' for plugin: ".$pluginName, WorkflowException::PLUGIN_MISSING);
        }

        $plugin = new $className();
        if (! $plugin instanceof IWorkflowPlugin)
        {
            throw new WorkflowException('Class for plugin is not instance of IWorkflowPlugin: '.$className, WorkflowException::PLUGIN_MISSING);
        }

        return $plugin;
    }

    /**
     * check if in interactive session
     */
    public function isInteractive()
    {
        return FALSE;
    }
}

?>