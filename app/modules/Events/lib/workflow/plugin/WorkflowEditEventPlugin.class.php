<?php

/**
 * This plugin takes care of editing Events.
 *
 * @author Thorsten Schmitt-Rink <thorstenn.schmitt-rink@berlinonline.de>
 * @version $Id: WorkflowEditEventPlugin.class.php 1058 2012-03-22 19:08:10Z tschmitt $
 * @package Events
 * @subpackage Workflow/Plugin
 */
class WorkflowEditEventPlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'event_edit';

    const GATE_ITEM_DELETE = 'delete';

    protected function getPluginAction()
    {
        return array(
            'module' => 'Events',
            'action' => 'Edit'
        );
    }

    protected function getPluginResource()
    {
        $workflowService = EventsWorkflowService::getInstance();
        return $workflowService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
