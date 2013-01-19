<?php

/**
 * This is the simplest plugin which does nothing than returning a success result
 * pointing to the promote gate.
 *
 * @author tay
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowNullPlugin extends WorkflowBasePlugin
{
    /**
     * Run our buisiness.
     * As a null plugin we always return a success result forwarding to our promote gate.
     *
     * @return IWorkflowPluginResult
     */
    protected function doProcess()
    {
        $result = new WorkflowPluginResult();
        $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
        $result->freeze();

        return $result;
    }
}
