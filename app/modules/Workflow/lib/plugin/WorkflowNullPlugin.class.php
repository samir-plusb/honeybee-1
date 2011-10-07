<?php
/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowNullPlugin extends IWorkflowPlugin
{
    public function initialize(WorkflowTicket $ticket, array $parameters)
    {
        return $this;
    }

    public function process()
    {
        return new WorkflowPluginResult(WorkflowPluginResult::OK, 0);
    }

    public function processRequest(AgaviParameterHolder $rd)
    {
        return $this->process();
    }

    public function isInteractive()
    {
        return FALSE;
    }
}