<?php
/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowNullPlugin implements IWorkflowPlugin
{
    public function initialize(WorkflowTicket $ticket, array $parameters)
    {
        return $this;
    }

    public function process()
    {
        return new WorkflowPluginResult(WorkflowPluginResult::STATE_OK, WorkflowPluginResult::GATE_DEFAULT);
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