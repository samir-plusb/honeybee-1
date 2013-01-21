<?php

/**
 * The WorkflowManager
 * * aims as factory for workflow handlers and tickets
 * * acts as interface to the UI
 *
 * @package Workflow
 * @author tay
 *
 * Basic workflow constraints are as follow:
 * There may be IWorkflowItems without tickets (new item) but no WorkflowTicke without an IWorkflowItem.
 * When a ticket without an item is encountered the supervisor raises an exception to propagate the inconsistence
 * and prevent the domain from corrupting our data's integrity.
 */
class WorkflowManager
{
    private $module;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function initWorkflowFor(IWorkflowResource $resource)
    {
        $workflowName = AgaviConfig::get(
            sprintf('%s.workflow', $this->getModule()->getOption('prefix'))
        );
        $workflow = $this->fetchCleanWorkflow($workflowName, $resource);

        $resource->setWorkflowTicket(array(
            'workflowName' => $workflow->getName(),
            'owner' => 'nobody',
            'workflowStep' => $workflow->getFirstStep(),
            'stepCounts' => array(),
            'lastResult' => array(
                'state' => NULL,
                'gate' => NULL,
                'message' =>  NULL
            )
        ));
    }

    public function getPossibleGates(IWorkflowResource $resource)
    {
        $ticket = $resource->getWorkflowTicket();
        $workflow = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
        $plugin = $workflow->getPluginFor($ticket->getWorkflowStep());

        return $workflow->getGatesForStep($ticket->getWorkflowStep());
    }

    public function isInInteractiveState(IWorkflowResource $resource)
    {
        $ticket = $resource->getWorkflowTicket();
        $workflow = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
        $plugin = $workflow->getPluginFor($ticket->getWorkflowStep());

        return $plugin instanceof WorkflowInteractivePlugin;
    }

    /**
     * Run the workflow for given resource.
     *
     * @param IWorkflowResource $resource
     * @param AgaviExecutionContainer $container execution container in interactive mode
     *
     * @return AgaviExecutionContainer or NULL
     *
     * @throws WorkflowException
     */
    public function executeWorkflowFor(IWorkflowResource $resource, $startGate = NULL, AgaviExecutionContainer $container = NULL)
    {
        $code = Workflow::STATE_NEXT_WORKFLOW;
        $pluginResult = NULL;
        $ticket = $resource->getWorkflowTicket();

        while (Workflow::STATE_NEXT_WORKFLOW === $code)
        {
            $workflow = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
            $resultData = $workflow->run($resource, $startGate, $container);
            $code = $resultData['code'];
            $pluginResult = $resultData['result'];
        }

        if (Workflow::STATE_ERROR === $code)
        {
            $message = $pluginResult->getMessage()
                ? $pluginResult->getMessage()
                : 'Workflow halted with error'; // Default err-message in case whoever forgot to provide one.

            throw new WorkflowException($message, WorkflowException::UNEXPECTED_EXIT_CODE);
        }

        $resource->getWorkflowTicket()->setLastResult(array(
            'state' => $pluginResult->getState(),
            'gate' => $pluginResult->getGate(),
            'message' => $pluginResult->getMessage()
        ));

        $this->getModule()->getService()->save($resource);

        return $pluginResult;
    }

    /**
     * get a new Workflow instance for a named workflow
     *
     * Workflows are defined by XML files under directory {@see WORKFLOW_CONFIG_DIR}
     *
     * @throws WorkflowExceptionon unreadable workflow configuration, etc.
     * @param string $name name of workflow
     * @return Workflow
     */
    protected function fetchCleanWorkflow($name, IWorkflowResource $resource)
    {
        $name = strtolower($name);

        if (! preg_match('/^_?[a-z][_\-\.a-z-0-9]+$/', $name))
        {
            throw new WorkflowException(
               'Workflow name contains invalid characters: '.$name,
                WorkflowException::INVALID_WORKFLOW_NAME
            );
        }

        $request = AgaviContext::getInstance()->getRequest();
        $namespace = __CLASS__.'.WorkFlow';
        $workflow = $request->getAttribute($name, $namespace, NULL);

        if (! $workflow)
        {
            try
            {
                $config = include AgaviConfigCache::checkConfig(
                    $resource->getWorkflowConfigPath()
                );
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

            $workflow = new Workflow($config['workflow']);
            $request->setAttribute($name, $workflow, $namespace);
        }

        // return only fresh instances
        $handler = clone $workflow;
        $handler->setWorkflowManager($this);

        return $handler;
    }
}
