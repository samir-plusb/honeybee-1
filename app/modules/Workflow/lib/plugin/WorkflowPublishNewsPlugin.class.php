<?php

/**
 * This plugin takes care of publishing news to the various subscribers.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowPublishNewsPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'publish_news';

    const GATE_ARCHIV = 'archiv';

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
        $result->setState(WorkflowPluginResult::STATE_OK);
        $result->setGate(self::GATE_ARCHIV);
        $result->setMessage('Ready to get the disposition to whereever rockin!');
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
