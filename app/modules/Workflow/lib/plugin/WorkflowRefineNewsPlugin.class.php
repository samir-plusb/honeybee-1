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
}

?>
