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

class Workflow_SupervisorModel extends ProjectBaseModel implements AgaviISingletonModel
{
    /**
     * database config name
     */
    const DATABASE_CONFIG_NAME = 'CouchWorkflow';

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
     *
     * @var WorkflowItemPeer
     */
    private $itemPeer;

    /**
     *
     * @var WorkflowTicketPeer
     */
    private $ticketPeer;

    /**
     * get a singleton instance for this model
     *
     * @return Workflow_SupervisorModel
     */
    static function getInstance()
    {
        return AgaviContext::getInstance()->getModel('Supervisor', 'Workflow');
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
    public function getDatabase()
    {
        return $this->couchClient;
    }


    /**
     * get item peer handler instance to access import items in the database
     *
     * @return WorkflowItemPeer
     */
    public function getItemPeer()
    {
        if (! $this->itemPeer)
        {
            $this->itemPeer = new WorkflowItemPeer();
        }
        return $this->itemPeer;
    }


    /**
     * get ticket peer handler instance to access tickets in the database
     *
     * @return WorkflowTicketPeer
     */
    public function getTicketPeer()
    {
        if (! $this->ticketPeer)
        {
            $this->ticketPeer = new WorkflowTicketPeer($this->getDatabase());
        }
        return $this->ticketPeer;
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
        $sender = $event->getSender();
        $name = $event->getName();
        if ($name !== BaseDataImport::EVENT_RECORD_SUCCESS || !($sender instanceof WorkflowItemDataImport))
        {
            return;
        }

        $data = $event->getData();
        if (! isset($data['record']) || ! $data['record'] instanceof IDataRecord)
        {
            return;
        }

        $ticket = $this->getTicketPeer()->getTicketByWorkflowItem($data['record']);
        $this->processTicket($ticket);
    }

    /**
     * process a ticket in a workflow until the workflow is ended or stopped
     *
     * @param WorkflowTicket $ticket
     * @param AgaviExecutionContainer $container execution container in interactive mode
     *
     * @return AgaviExecutionContainer or NULL
     *
     * @throws WorkflowException
     */
    public function processTicket(WorkflowTicket $ticket, AgaviExecutionContainer $container = NULL)
    {
        $code = WorkflowHandler::STATE_NEXT_WORKFLOW;
        while (WorkflowHandler::STATE_NEXT_WORKFLOW === $code)
        {
            $workflow = $this->getWorkflowByName($ticket->getWorkflow());
            $ticket->setExecutionContainer($container);
            $code = $workflow->run($ticket);
        }

        if (WorkflowHandler::STATE_ERROR == $code)
        {
            $message = $ticket->getPluginResult()->getMessage()
                ? $ticket->getPluginResult()->getMessage()
                : 'Workflow halted with error';
            throw new WorkflowException($message, WorkflowException::UNEXPECTED_EXIT_CODE);
        }

        if ($ticket->getPluginResult() instanceof WorkflowInteractivePluginResult)
        {
            return $ticket->getPluginResult()->getResponse();
        }
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
        $request = AgaviContext::getInstance()->getRequest();
        $namespace = __CLASS__.'.WorkFlow';
        $workflow = $request->getAttribute($name, $namespace, NULL);
        if (! $workflow)
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
            $workflow = new WorkflowHandler($config['workflow']);
            $request->setAttribute($name, $workflow, $namespace);
        }

        // return only fresh instances
        return clone $workflow;
    }

}

?>