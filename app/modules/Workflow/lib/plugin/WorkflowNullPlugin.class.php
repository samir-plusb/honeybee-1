<?php

/**
 * This is the simplest plugin which does nothing than returning a success result
 * pointing to the promote gate.
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowNullPlugin extends WorkflowBasePlugin
{
    /**
     * Holds the plugin id.
     */
    const PLUGIN_ID = 'null_plugin';

    /**
     * Holds the name of the plugin's promote gate.
     */
    const GATE_PROMOTE = 'promote';

    /**
     * Return an id that is unqiue to the plugin type.
     *
     * @return string
     */
    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    /**
     * Run our buisiness.
     * As a null plugin we always return a success result forwarding to our promote gate.
     *
     * @return IWorkflowPluginResult
     */
    protected function doProcess()
    {
        $result = new WorkflowPluginResult();
        $result->setState(WorkflowPluginResult::STATE_OK);
        $result->setGate(self::GATE_PROMOTE);
        $result->freeze();
        return $result;
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     * As a null plugin we always allow execution.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        return TRUE;
    }
}

?>
