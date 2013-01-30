<?php

namespace Honeybee\Core\Workflow;

use Honeybee\Core\Dat0r\Module;

/**
 * The Workflow\Manager
 * * aims as factory for workflow handlers and tickets
 * * acts as interface to the UI
 *
 * @author tay
 *
 * Basic workflow constraints are as follow:
 * There may be IWorkflowItems without tickets (new item) but no WorkflowTicke without an IWorkflowItem.
 * When a ticket without an item is encountered the supervisor raises an exception to propagate the inconsistence
 * and prevent the domain from corrupting our data's integrity.
 */
class Manager
{
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function initWorkflowFor(IResource $resource)
    {
        $workflowName = \AgaviConfig::get(
            sprintf('%s.workflow', $this->getModule()->getOption('prefix'))
        );
        $execution = $this->fetchCleanWorkflow($workflowName, $resource);

        $resource->setWorkflowTicket(array(
            'workflowName' => $execution->getName(),
            'owner' => 'nobody',
            'workflowStep' => $execution->getFirstStep(),
            'stepCounts' => NULL,
            'lastResult' => array(
                'state' => NULL,
                'gate' => NULL,
                'message' =>  NULL
            )
        ));
    }

    public function getPossibleGates(IResource $resource)
    {
        $ticket = $resource->getWorkflowTicket();
        $execution = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
        $plugin = $execution->getPluginFor($ticket->getWorkflowStep());

        return $execution->getGatesForStep($ticket->getWorkflowStep());
    }

    public function isInInteractiveState(IResource $resource)
    {
        $ticket = $resource->getWorkflowTicket();
        $execution = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
        $plugin = $execution->getPluginFor($ticket->getWorkflowStep());

        return $plugin instanceof Plugin\InteractivePlugin;
    }

    /**
     * Run the workflow for given resource.
     *
     * @param IResource $resource
     * @param AgaviExecutionContainer $container execution container in interactive mode
     * @todo If we'll stick with this workflow solution, we need to somehow factor out the container.
     *       This AgaviExecutionContainer dependency is kinda broken.
     *
     * @return AgaviExecutionContainer or NULL
     *
     * @throws Exception
     */
    public function executeWorkflowFor(IResource $resource, $startGate = NULL, \AgaviExecutionContainer $container = NULL)
    {
        $code = Process::STATE_NEXT_WORKFLOW;
        $pluginResult = NULL;
        $ticket = $resource->getWorkflowTicket();

        while (Process::STATE_NEXT_WORKFLOW === $code)
        {
            $workflow = $this->fetchCleanWorkflow($ticket->getWorkflowName(), $resource);
            $resultData = $workflow->execute($resource, $startGate, $container);
            $code = $resultData['code'];
            $pluginResult = $resultData['result'];
        }

        if (Process::STATE_ERROR === $code)
        {
            $message = $pluginResult->getMessage()
                ? $pluginResult->getMessage()
                : 'Process halted with error'; // Default err-message in case whoever forgot to provide one.

            throw new Exception($message, Exception::UNEXPECTED_EXIT_CODE);
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
     * get a new Process instance for a named workflow
     *
     * Workflows are defined by XML files under directory {@see WORKFLOW_CONFIG_DIR}
     *
     * @throws Exceptionon unreadable workflow configuration, etc.
     * @param string $name name of workflow
     * @return Process
     */
    protected function fetchCleanWorkflow($name, IResource $resource)
    {
        $name = strtolower($name);

        if (! preg_match('/^_?[a-z][_\-\.a-z-0-9]+$/', $name))
        {
            throw new Exception(
               'Workflow name contains invalid characters: '.$name,
                Exception::INVALID_WORKFLOW_NAME
            );
        }

        $request = \AgaviContext::getInstance()->getRequest();
        $namespace = __CLASS__.'.WorkFlow';
        $workflow = $request->getAttribute($name, $namespace, NULL);

        if (! $workflow)
        {
            try
            {
                $config = include \AgaviConfigCache::checkConfig(
                    $resource->getWorkflowConfigPath()
                );
            }
            catch (\AgaviUnreadableException $e)
            {
                throw new Exception($e->getMessage(), Exception::WORKFLOW_NOT_FOUND, $e);
            }

            if (! array_key_exists('workflow', $config))
            {
                throw new Exception(
                    'Workflow definition structure is invalid.',
                    Exception::INVALID_WORKFLOW
                );
            }

            $workflow = new Process($config['workflow']);
            $request->setAttribute($name, $workflow, $namespace);
        }

        // return only fresh instances
        $handler = clone $workflow;
        $handler->setWorkflowManager($this);

        return $handler;
    }
}
