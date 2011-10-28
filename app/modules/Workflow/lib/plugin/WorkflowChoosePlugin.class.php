<?php

/**
 * This is the simplest interactive plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowChoosePlugin extends WorkflowBaseInteractivePlugin implements IWorkflowPlugin
{
    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::process()
     */
    public function process()
    {
        $container = $this->ticket->getExecutionContainer();

        $state = WorkflowPluginResult::STATE_OK;
        $gate = $container->getParameter('gate', WorkflowPluginResult::GATE_NONE);

        if (WorkflowPluginResult::GATE_NONE == $gate)
        {
            $state =  WorkflowPluginResult::STATE_EXPECT_INPUT;
            $gate = WorkflowPluginResult::GATE_NONE;
        }

        $response = $this->createResponseContainer('Plugin_Choose');
        return new WorkflowInteractivePluginResult($response, $state, $gate);
    }
}