<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow;

/**
 * The BasePlugin serves as the base implementation of the IWorkflowPlugin interface,
 * that all other IWorkflowPlugin implementations should inherit from.
 *
 * @author tay
 */
abstract class BasePlugin implements Workflow\IPlugin
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

    protected $workflowProcess;

    protected $stepName;

    /**
     * The doProcess template method is responseable for running the actual buisiness logic
     * of a concrete IWorkflowPlugin implementation.
     * In a common scenario this will be the only method you will need to implement when writing new plugins.
     * Usually you would then ask the ticket for it's workflow item, then do something important on the data
     * and finally return an IResult that reflects the result of the plugin's processing.
     *
     * @return Result
     */
    protected abstract function doProcess();

    /**
     * Initialize the plugin state.
     *
     * @param WorkflowTicket $ticket
     * @param string $stepName
     *
     * @return BasePlugin Return $this for fluent api support.
     */
    public function initialize(Workflow\Process $process, $stepName)
    {
        $this->workflowProcess = $process;
        $this->stepName = $stepName;

        $this->parameters = $this->workflowProcess->getParametersForStep($this->stepName);
        $this->gates = $this->workflowProcess->getGatesForStep($this->stepName);

        return $this;
    }

    /**
     * Execute the plugin's buisiness logic against it's ticket.
     *
     * @return Plugin\
     */
    public function process()
    {
        if ($this->mayProcess())
        {
            return $this->doProcess();
        }

        $result = new Result();
        $result->setState(Result::STATE_NOT_ALLOWED);

        $result->setMessage(
            sprintf("You do not own the required credentials to execute this plugin %s(%s)!",
            get_class($this),
            $this->getPluginId()
        ));
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

    public function getParameter($name, $default = NULL)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    public function isBreakPoint() 
    {   
        return $this->getParameter('is_breakpoint', FALSE);
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
            $action = sprintf(
                '%s::%s',
                $this->getPluginId(),
                $this->getWorkflow()->getContainer()->getRequestMethod()
            );

            return $user->isAllowed($this->getResource(), $action);
        }
        else
        {
            // @todo Depending on how we implement the maintenance api
            // it could be that we run into here during shell jobs.
            // before thinking about flexible user constraints, it might
            // be a good idea to have a system user or something like that for shell jobs etc.
            return TRUE;
        }
    }

    /**
     * Convenience method for fetching the current AgaviContext.
     *
     * @return AgaviContext
     */
    protected function getContext()
    {
        return \AgaviContext::getInstance();
    }

    protected function getWorkflow()
    {
        return $this->workflowProcess;
    }

    protected function getTicket()
    {
        return $this->getResource()->getWorkflowTicket();
    }

    protected function getResource()
    {
        return $this->getWorkflow()->getResource();
    }

    /**
     * Convenience method for logging errors to the application's default and
     * error log.
     *
     * @param string $msg
     */
    protected function logError($msg)
    {
        $this->getContext()->getLoggerManager()->logTo(null, \AgaviLogger::ERROR, get_class($this), $msg);
        $this->getContext()->getLoggerManager()->logTo('error', \AgaviLogger::ERROR, get_class($this), $msg);
    }

    /**
     * Convenience method for logging messages to the default application log.
     *
     * @param string $msg
     */
    protected function logInfo($msg)
    {
        $this->getContext()->getLoggerManager()->logTo(null, \AgaviLogger::ERROR, get_class($this), $msg);
    }
}
