<?php

class WorkflowRefineNewsPlugin extends WorkflowBaseInteractivePlugin
{
    protected function doProcess()
    {
        return $this->executePluginAction('Edit', 'Items');
    }
}
