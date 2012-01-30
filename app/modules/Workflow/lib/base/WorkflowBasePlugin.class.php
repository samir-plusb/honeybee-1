<?php

/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
abstract class WorkflowBasePlugin implements IWorkflowPlugin
{
    /**
     *
     * @var WorkflowTicket
     */
    protected $ticket;

    /**
     *
     * @var array
     */
    protected $parameters;

    /**
     *
     * @var array
     */
    protected $gates;

    /**
     * Do the actual processing of the plugin buisiness.
     */
    protected abstract function doProcess();

    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::initialize()
     */
    public function initialize(WorkflowTicket $ticket, array $parameters, array $gates)
    {
        $this->ticket = $ticket;
        $this->parameters = $parameters;
        $this->gates = $gates;
        return $this;
    }

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
     * return false to signalize a non interactive plugin by default
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
     * get a list of gate labels
     *
     * @return array
     */
    protected function getGates()
    {
        return $this->gates;
    }

    protected function logError($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('error');
        $errMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($errMsg, AgaviLogger::ERROR)
        );
    }

    protected function logInfo($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($infoMsg, AgaviLogger::INFO)
        );
    }

    protected function getContext()
    {
        return AgaviContext::getInstance();
    }
}

?>
