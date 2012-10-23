<?php

/**
 * This plugin takes care of editing shofi verticals.
 *
 * @author Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @version $Id$
 * @package Shofi_Verticals
 * @subpackage Workflow/Plugin
 */
class WorkflowEditShofiVerticalPlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'shofi_vertical_edit';

    const GATE_ITEM_DELETE = 'delete';

    protected function getPluginAction()
    {
        return array(
            'module' => 'Shofi_Verticals',
            'action' => 'Edit'
        );
    }

    protected function getPluginResource()
    {
        $workflowService = ShofiVerticalsWorkflowService::getInstance();
        return $workflowService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
