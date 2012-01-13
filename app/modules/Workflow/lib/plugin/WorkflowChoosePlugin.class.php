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
    const PLUGIN_ID = 'choose_something';

    const PARAM_MESSAGE = 'message';

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    protected function doProcess()
    {
        $result = NULL;
        if ($this->ticket->hasParameter('choose_step_executed'))
        {
            $result = new WorkflowPluginResult();
            $result->setGate('terminate');
            $result->setState(IWorkflowPluginResult::STATE_OK);
            $result->setMessage($this->parameters[self::PARAM_MESSAGE]);
            $result->freeze();
        }
        else
        {
            $result = parent::doProcess();
            if (IWorkflowPluginResult::STATE_OK === $result->getState())
            {
                $this->ticket->setParameter('choose_step_executed', 'yes');
            }
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see WorkflowBaseInteractivePlugin::doProcess()
     */
    protected function getPluginAction()
    {
        return array(
            'module' => 'Workflow',
            'action' => 'Plugin_Choose',
            'parameters' => array(
                'gates' => $this->getGates()
            )
        );
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        return TRUE;
    }
}

?>
