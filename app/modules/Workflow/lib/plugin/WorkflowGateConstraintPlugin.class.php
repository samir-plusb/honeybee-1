<?php

/**
 * This plugin takes care of marking news as deleted.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowGateConstraintPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'gate_constraint';

    const GATE_ALLOW = 'allow';

    const GATE_DENY = 'deny';

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    /**
     * (non-PHPdoc)
     * @see WorkflowBasePlugin::process()
     */
    protected function doProcess()
    {
        $result = new WorkflowPluginResult();
        $targetPlugin = $this->getTargetStep();

        // @todo verify the transition and choose either the allow or deny gate.
        $result->freeze();
        return $result;
    }

    protected function getTarget()
    {
        $workflow = Workflow_SupervisorModel::getInstance()->getWorkflowByName(
            $this->ticket->getWorkflow()
        );
        $step = $workflow->getStep($this->gates[self::GATE_ALLOW]);
        if (! $step)
        {
            return NULL;
        }
        return $workflow->getPluginFor($step);
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
