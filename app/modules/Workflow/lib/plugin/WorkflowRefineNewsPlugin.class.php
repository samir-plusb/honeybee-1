<?php

/**
 * This plugin takes care of news refinement,
 * hence displaying the correspondig form and handling data storage.
 *
 * @author tay
 * @version $Id: WorkflowRefineNewsPlugin.class.php 902 2012-02-13 23:22:49Z tschmitt $
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowRefineNewsPlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'refine_news';

    const GATE_ITEM_PUBLISH = 'publish_news';

    const GATE_ITEM_DELETE = 'delete_news';

    protected function getPluginAction()
    {
        return array(
            'module' => 'News',
            'action' => 'Edit'
        );
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
