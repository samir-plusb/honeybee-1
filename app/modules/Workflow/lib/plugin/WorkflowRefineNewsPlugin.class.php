<?php

class WorkflowRefineNewsPlugin extends WorkflowBaseInteractivePlugin
{
    protected function getPluginAction()
    {
        return array(
            'module' => 'Items',
            'action' => 'Edit'
        );
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        $user = $this->ticket->getSessionUser();
        if (! $user)
        {
            return FALSE;
        }
        $user->isAllowed(
            $this->ticket->getWorkflowItem(),
            'write'
        );
        return TRUE;
    }
}

?>
