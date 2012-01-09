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
    const GATE_PROMOTE = 'promote';

    const PARAM_MESSAGE = 'message';

    /**
     * (non-PHPdoc)
     * @see WorkflowBasePlugin::process()
     */
    protected function doProcess()
    {
        $result = new WorkflowPluginResult();
        $result->setState(WorkflowPluginResult::STATE_OK);
        $result->setGate(self::GATE_PROMOTE);
        $result->freeze();
        return $result;
    }
}

?>
