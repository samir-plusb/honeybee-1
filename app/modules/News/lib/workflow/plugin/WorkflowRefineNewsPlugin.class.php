<?php

/**
 * This plugin takes care of news refinement,
 * hence displaying the correspondig form and handling data storage.
 *
 * @author tay
 * @version $Id$
 * @package News
 * @subpackage Workflow/Plugin
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

    protected function getPluginResource()
    {
        $newsService = NewsWorkflowService::getInstance();
        return $newsService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
