<?php

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
