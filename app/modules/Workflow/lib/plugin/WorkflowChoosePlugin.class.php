<?php

/**
 * This is the simplest interactive plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowChoosePlugin extends WorkflowBaseInteractivePlugin
{
    /**
     * (non-PHPdoc)
     * @see WorkflowBaseInteractivePlugin::doProcess()
     */
    protected function getPluginAction()
    {
        return array(
            'module'     => 'Workflow',
            'action'     => 'Plugin_Choose',
            'parameters' => array(
                'gates' => $this->getGates()
            )
        );
    }
}

?>
