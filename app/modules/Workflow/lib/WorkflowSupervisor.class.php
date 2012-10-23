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
class WorkflowSupervisor
{
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
     * @var WorkflowItemStore
     */
    private $workflowItemStore;

    /**
     *
     * @var WorkflowTicketStore
     */
    private $workflowTicketStore;

    public function initialize(ExtendedCouchDbClient $client)
    {
        $this->couchClient = $client;
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
     * Return the IDocumentStore instance responseable for handling WorkflowItems.
     *
     * @return WorkflowItemStore
     */
    public function getWorkflowItemStore()
    {
        if (! $this->workflowItemStore)
        {
            $this->workflowItemStore = new WorkflowItemStore($this->getDatabase());
        }
        return $this->workflowItemStore;
    }

    /**
     * Return the IDocumentStore instance responseable for handling WorkflowTickets.
     *
     * @return WorkflowTicketStore
     */
    public function getWorkflowTicketStore()
    {
        if (! $this->workflowTicketStore)
        {
            $this->workflowTicketStore = new WorkflowTicketStore($this->getDatabase());
        }
        return $this->workflowTicketStore;
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
        if (($ticket = $this->getWorkflowTicketStore()->getTicketByWorkflowItem($item)))
        {
            throw new WorkflowException("Received create notification for an existing ticket");
        }

        $ticket = $this->getWorkflowTicketStore()->createTicketByWorkflowItem($item);
        $item->setTicketId($ticket->getIdentifier());
        $this->getWorkflowItemStore()->save($item);
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
        $ticket = $this->getWorkflowTicketStore()->getTicketByWorkflowItem($item);
        if (! $ticket)
        {
            throw new WorkflowException("Received update notification for a non-existing ticket");
        }
        // not clear what to do here at the moment. either call processTicket or set flag...
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
                : 'Workflow halted with error'; // Default err-message in case whoever forgot to provide one.
            throw new WorkflowException($message, WorkflowException::UNEXPECTED_EXIT_CODE);
        }

        /**
         * Sync our item's state with the ticket for search/data convenience,
         * as we can now ask an item about it's (eventuell)state without needing to refer to it's tickets.
         */
        $item = $this->getWorkflowItemStore()->fetchByIdentifier($ticket->getItem());
        $this->getWorkflowItemStore()->save(
            $item->setCurrentState(array(
                'workflow' => $ticket->getWorkflow(),
                'step'     => $ticket->getCurrentStep(),
                'owner'    => $ticket->getCurrentOwner()
            ))
        );

        return $pluginResult;
    }

    public function proceed(WorkflowTicket $ticket, $startGate, AgaviExecutionContainer $container = NULL)
    {
        $code = WorkflowHandler::STATE_NEXT_WORKFLOW;
        while (WorkflowHandler::STATE_NEXT_WORKFLOW === $code)
        {
            $workflow = $this->getWorkflowByName($ticket->getWorkflow());
            $ticket->setExecutionContainer($container);
            $code = $workflow->run($ticket, $startGate);
        }

        $pluginResult = $ticket->getPluginResult();
        if (WorkflowHandler::STATE_ERROR === $code)
        {
            $message = $pluginResult->getMessage()
                ? $pluginResult->getMessage()
                : 'Workflow halted with error'; // Default err-message in case whoever forgot to provide one.
            throw new WorkflowException($message, WorkflowException::UNEXPECTED_EXIT_CODE);
        }

        /**
         * Sync our item's state with the ticket for search/data convenience,
         * as we can now ask an item about it's (eventuell)state without needing to refer to it's tickets.
         */
        $item = $this->getWorkflowItemStore()->fetchByIdentifier($ticket->getItem());
        $this->getWorkflowItemStore()->save(
            $item->setCurrentState(array(
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
        if (! preg_match('/^_?[a-z][_a-z-0-9]+$/', $name))
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
            $customWorkflows = AgaviConfig::get('workflow.workflows', array());
            if (isset($customWorkflows[$name]))
            {
                $configPath = $customWorkflows[$name];
            }
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
        $handler = clone $workflow;
        $handler->setSupervisor($this);
        return $handler;
    }
}

?>
