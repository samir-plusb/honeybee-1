<?php

/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowNullPlugin extends WorkflowBasePlugin
{

    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::process()
     */
    protected function doProcess()
    {
        return new WorkflowPluginResult(WorkflowPluginResult::STATE_OK, WorkflowPluginResult::GATE_DEFAULT);
    }
}