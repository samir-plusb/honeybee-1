<?php

/**
 * This plugin takes care of editing shofi categories.
 *
 * @author Thorsten Schmitt-Rink <thorstenn.schmitt-rink@berlinonline.de>
 * @version $Id: WorkflowEditShofiCategoryPlugin.class.php 1058 2012-03-22 19:08:10Z tschmitt $
 * @package Shofi_Categories
 * @subpackage Workflow/Plugin
 */
class WorkflowEditShofiCategoryPlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'shofi_category_edit';

    const GATE_ITEM_DELETE = 'delete';

    protected function getPluginAction()
    {
        return array(
            'module' => 'Shofi_Categories',
            'action' => 'Edit'
        );
    }

    protected function getPluginResource()
    {
        $workflowService = ShofiCategoriesWorkflowService::getInstance();
        return $workflowService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
