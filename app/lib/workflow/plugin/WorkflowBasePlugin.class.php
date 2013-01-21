<?php

/**
 * The WorkflowBasePlugin serves as the base implementation of the IWorkflowPlugin interface,
 * that all other IWorkflowPlugin implementations should inherit from.
 *
 * @author tay
 * @package Workflow
 * @subpackage Plugin
 */
abstract class WorkflowBasePlugin implements IWorkflowPlugin
{
    /**
     * Holds an assoc array with an arbitary number of key-value pairs.
     *
     * @var array
     */
    protected $parameters;

    /**
     * A list of gates available for the current plugin instance.
     *
     * @var array
     */
    protected $gates;

    protected $workflow;

    protected $stepName;

    /**
     * The doProcess template method is responseable for running the actual buisiness logic
     * of a concrete IWorkflowPlugin implementation.
     * In a common scenario this will be the only method you will need to implement when writing new plugins.
     * Usually you would then ask the ticket for it's workflow item, then do something important on the data
     * and finally return an IWorkflowPluginResult that reflects the result of the plugin's processing.
     *
     * @return IWorkflowPluginResult
     */
    protected abstract function doProcess();

    /**
     * Initialize the plugin state.
     *
     * @param WorkflowTicket $ticket
     * @param string $stepName
     *
     * @return WorkflowBasePlugin Return $this for fluent api support.
     */
    public function initialize(Workflow $workflow, $stepName)
    {
        $this->workflow = $workflow;
        $this->stepName = $stepName;

        $this->parameters = $this->workflow->getParametersForStep($this->stepName);
        $this->gates = $this->workflow->getGatesForStep($this->stepName);

        return $this;
    }

    /**
     * Execute the plugin's buisiness logic against it's ticket.
     *
     * @return WorkflowPluginResult
     */
    public function process()
    {
        if ($this->mayProcess())
        {
            return $this->doProcess();
        }

        $result = new WorkflowPluginResult();
        $result->setState(WorkflowPluginResult::STATE_NOT_ALLOWED);
        $result->setMessage(
            "You do not own the required credentials to execute this plugin (" . get_class($this)  . ")!"
        );
        $result->freeze();

        return $result;
    }

    /**
     * Tells whether as plugin is interactive or not.
     * @todo Maybe replace this method by a marker interface IInteractiveWorkflowPlugin and use instanceof checks.
     *
     * @see IWorkflowPlugin::isInteractive()
     *
     * @return boolean
     */
    public function isInteractive()
    {
        return FALSE;
    }

    public function getPluginId()
    {
        return sprintf(
            '%s.%s', 
            $this->getResource()->getResourceId(), 
            $this->getStepName()
        );
    }

    /**
     * Invoked when the plugin is left by a resource through one of it's gates.
     */
    public function onResourceLeaving($gateName){}

    /**
     * Invoked when the plugin is entered by a resourced.
     */
    public function onResourceEntered($prevStepName){}

    /**
     * Return an array containing the names of all gates
     * that are available for the plugin instance.
     *
     * @return array
     */
    public function getGates()
    {
        return $this->gates;
    }

    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * Return the plugin's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : NULL;
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     * We provide the most restrictive base in order to prevent insecure plugins from being created 'by mistake'.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        if (($user = $this->getWorkflow()->getSessionUser()))
        {
            $operation = sprintf(
                '%s::%s',
                $this->getPluginId(),
                $this->getWorkflow()->getContainer()->getRequestMethod()
            );

            return $user->isAllowed($this->getResource(), $operation);
        }
        else
        {
            // @todo Depending on how we implement the maintenance api
            // it could be that we run into here during shell jobs.
            // before thinking about flexible user constraints, it might
            // be a good idea to have a system user or something like that for shell jobs etc.
        }

        return FALSE;
    }

    /**
     * Convenience method for logging errors to the application's error log.
     *
     * @param string $msg
     */
    protected function logError($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('error');
        $errMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($errMsg, AgaviLogger::ERROR)
        );
    }

    /**
     * Convenience method for logging messages to the application's info log.
     *
     * @param string $msg
     */
    protected function logInfo($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($infoMsg, AgaviLogger::INFO)
        );
    }

    /**
     * Convenience method for fetching the current AgaviContext.
     *
     * @return AgaviContext
     */
    protected function getContext()
    {
        return AgaviContext::getInstance();
    }

    protected function getWorkflow()
    {
        return $this->workflow;
    }

    protected function getTicket()
    {
        return $this->getResource()->getWorkflowTicket();
    }

    protected function getResource()
    {
        return $this->getWorkflow()->getResource();
    }
}
