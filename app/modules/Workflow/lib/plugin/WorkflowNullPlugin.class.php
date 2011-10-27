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
    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::initialize()
     */
    public function initialize(WorkflowTicket $ticket, array $parameters)
    {
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::process()
     */
    public function process()
    {
        return new WorkflowPluginResult(WorkflowPluginResult::STATE_OK, WorkflowPluginResult::GATE_DEFAULT);
    }

    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::isInteractive()
     */
    public function isInteractive()
    {
        return FALSE;
    }
}