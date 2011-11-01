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
        return $this->executePluginAction('Plugin_Choose');
    }
}