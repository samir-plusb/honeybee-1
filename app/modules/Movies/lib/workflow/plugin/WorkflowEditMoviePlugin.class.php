<?php

/**
 * This plugin takes care of editing movies.
 *
 * @author Thorsten Schmitt-Rink <thorstenn.schmitt-rink@berlinonline.de>
 * @version $Id$
 * @package Movies
 * @subpackage Workflow/Plugin
 */
class WorkflowEditMoviePlugin extends WorkflowBaseInteractivePlugin
{
    const PLUGIN_ID = 'movie_edit';

    const GATE_ITEM_DELETE = 'delete';

    protected function getPluginAction()
    {
        return array(
            'module' => 'Movies',
            'action' => 'Edit'
        );
    }

    protected function getPluginResource()
    {
        $workflowService = MoviesWorkflowService::getInstance();
        return $workflowService->fetchWorkflowItemById($this->ticket->getItem());
    }

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }
}

?>
