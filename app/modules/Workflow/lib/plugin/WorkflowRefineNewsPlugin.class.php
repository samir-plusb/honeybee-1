<?php

class WorkflowRefineNewsPlugin extends WorkflowBaseInteractivePlugin
{
    const GATE_ITEM_PUBLISH = 'publish_item';

    const GATE_ITEM_DELETE = 'delete_item';

    protected static $operationsMap = array(
        'read'  => 'view_edit_form',
        'write' => 'edit_item'
    );

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

        return $user->isAllowed(
            $this->ticket->getWorkflowItem(),
            $this->ticket->getExecutionContainer()->getRequestMethod()
        );
    }
}

?>
