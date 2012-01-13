<?php

/**
 * This plugin takes care of marking news as deleted.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowDeleteNewsPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'delete_news';

    const GATE_TRASH = 'trash';

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
        try
        {
            $this->ticket->getWorkflowItem()->delete();
            $result->setState(WorkflowPluginResult::STATE_OK);
            $result->setGate(self::GATE_TRASH);
        }
        catch(CouchdbClientException $e)
        {
            $result = new WorkflowPluginResult();
            $result->setState(WorkflowPluginResult::STATE_ERROR);
            $result->setMessage($e->getMessage());
        }
        $result->freeze();
        return $result;
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
