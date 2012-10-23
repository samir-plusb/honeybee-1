<?php

class Events_EditAction extends EventsBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $ticket = $parameters->getParameter('ticket');
        $workflowService = EventsWorkflowService::getInstance();

        if ($ticket)
        {
            $eventContainer = $workflowService->fetchWorkflowItemById(
                $ticket->getItem()
            );
            $this->setAttribute('ticket', $ticket);
        }
        else
        {
            $eventContainer = $workflowService->createWorkflowItem(array(
                'masterRecord' => array()
            ));
        }

        $this->setAttribute('item', $eventContainer);
        // The plugin result is passed on to any underlying (plugin)actions via attribute value.
        // All actions that are called from a workflow plugin must set the gate,
        // state and message on the injected result object.
        // If we have no pluginResult set and our container does not have a 'is_workflow_container' set to true,
        // then we have not been invoked from workflow execution.
        // Depending on the action's semantics and eventuell assumptions concerning out execution context,
        // we should either termininate with an exception or switch the logic.
        $this->setContainerPluginState();

        return 'Input';
    }

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        throw new Exception("Not implemented yet!");
    }

    protected function setContainerPluginState()
    {
        $pluginResult = $this->getContainer()->getAttribute(
            WorkflowBaseInteractivePlugin::ATTR_RESULT,
            WorkflowBaseInteractivePlugin::NS_PLUGIN_ATTRIBUTES
        );
        if ($pluginResult)
        {
            $pluginResult->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
        }
    }
}

?>
