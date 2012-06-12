<?php

/**
 * The WorkflowBasePlugin serves as the base implementation of the IWorkflowPlugin interface,
 * that all other IWorkflowPlugin implementations should inherit from.
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Plugin
 */
abstract class WorkflowBasePlugin implements IWorkflowPlugin
{
    /**
     * Holds the ticket we are to process.
     *
     * @var WorkflowTicket
     */
    protected $ticket;

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
     * @param array $parameters
     * @param array $gates
     *
     * @return WorkflowBasePlugin Return $this for fluent api support.
     */
    public function initialize(WorkflowTicket $ticket, array $parameters, array $gates)
    {
        $this->ticket = $ticket;
        $this->parameters = $parameters;
        $this->gates = $gates;
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

    /**
     * Returns whether the plugin is executable at the current app/session state.
     * We provide the most restrictive base in order to prevent insecure plugins from being created 'by mistake'.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        return FALSE;
    }

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

    /**
     * Return the plugin's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
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
}

?>
