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
 * Basic workflow constraints are as follow:
 * There may be IWorkflowItems without tickets (new item) but no WorkflowTicke without an IWorkflowItem.
 * When a ticket without an item is encountered the supervisor raises an exception to propagate the inconsistence
 * and prevent the domain from corrupting our data's integrity.
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
            $this->itemPeer = new WorkflowItemPeer($this->getDatabase());
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
     * Notifies the supervisor that a workflow item has been created from outside the workflow.
     * At the moment this only happens during import.
     *
     * @param IWorkflowItem $item
     *
     * @throws WorkflowException When a ticket for the workflow item allready exists.
     */
    public function onWorkflowItemCreated(IWorkflowItem $item)
    {
        $ticket = $this->getTicketPeer()->getTicketByWorkflowItem($item);
        if ($ticket)
        {
            throw new WorkflowException("Received create notification for an existing ticket");
        }

        $ticket = $this->getTicketPeer()->createTicketByWorkflowItem($item);
        $item->setTicket($ticket);
        $this->processTicket($ticket);
    }

    /**
     * Notifies the supervisor that a workflow item has been updated from outside the workflow.
     * At the moment this only happens during import.
     *
     * @param IWorkflowItem $item
     *
     * @throws WorkflowException When a ticket for the workflow item does not exist.
     */
    public function onWorkflowItemUpdated(IWorkflowItem $item)
    {
        $ticket = $this->getTicketPeer()->getTicketByWorkflowItem($item);
        if (! $ticket)
        {
            throw new WorkflowException("Received update notification for a non-existing ticket");
        }
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

        $pluginResult = $ticket->getPluginResult();
        if (WorkflowHandler::STATE_ERROR === $code)
        {
            $message = $pluginResult->getMessage()
                ? $pluginResult->getMessage()
                : 'Workflow halted with error'; // Default err-message in case someone messy forgot to provide one.
            throw new WorkflowException($message, WorkflowException::UNEXPECTED_EXIT_CODE);
        }

        /**
         * Sync our item's state with the ticket for search/data convenience,
         * as we can now ask an item about it's (eventuell)state without needing to refer to it's tickets.
         */
        $this->getItemPeer()->storeItem(
            $ticket->getWorkflowItem()->updateCurrentState(array(
                'workflow' => $ticket->getWorkflow(),
                'step'     => $ticket->getCurrentStep(),
                'owner'    => $ticket->getCurrentOwner()
            ))
        );
        return $pluginResult;
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
