<?php

/**
 * This plugin takes care of news refinement,
 * hence displaying the correspondig form and handling data storage.
 *
 * @author Thorsten Schmitt-Rink <thorstenn.schmitt-rink@berlinonline.de>
 * @version $Id: WorkflowEditPlacePlugin.class.php 1058 2012-03-22 19:08:10Z tschmitt $
 * @package Shofi
 * @subpackage Workflow/Plugin
 */
class WorkflowEditPlacePlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'edit_places';

    const GATE_ITEM_PUBLISH = 'publish_place';

    const GATE_ITEM_DELETE = 'delete_place';

    protected function getPluginAction()
    {
        return array(
            'module' => 'Shofi',
            'action' => 'Edit'
        );
    }

    protected function getPluginResource()
    {
        $shofiService = ShofiWorkflowService::getInstance();
        return $shofiService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
