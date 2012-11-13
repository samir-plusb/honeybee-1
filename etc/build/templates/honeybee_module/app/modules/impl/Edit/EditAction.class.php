<?php

class Movies_EditAction extends MoviesBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $ticket = $parameters->getParameter('ticket');
        $workflowService = MoviesWorkflowService::getInstance();

        if ($ticket)
        {
            $movieContainer = $workflowService->fetchWorkflowItemById(
                $ticket->getItem()
            );
            $this->setAttribute('ticket', $ticket);
        }
        else
        {
            $movieContainer = $workflowService->createWorkflowItem(array(
                'masterRecord' => array()
            ));
        }

        $this->setAttribute('item', $movieContainer);
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
        $view = 'Success';
        $errors = array();
        try
        {
            $ticket = $parameters->getParameter('ticket');
            $workflowService = MoviesWorkflowService::getInstance();
            $movieItem = NULL;
            if (! $ticket)
            {
                $movieItem = $workflowService->createWorkflowItem(array(
                    'masterRecord' => $parameters->getParameter('movie', array())
                ));
            }
            else
            {   
                $movieItem = $workflowService->fetchWorkflowItemById(
                    $ticket->getItem()
                );
                $movieItem->updateMasterRecord(
                    $parameters->getParameter('movie', array())
                );
            }
            
            if ($parameters->hasParameter('attributes'))
            {
                $movieItem->setAttributes(
                    $parameters->getParameter('attributes')
                );
            }

            if (! $workflowService->storeWorkflowItem($movieItem))
            {
                $errors[] = $transManager->_('storage_error', 'shofi.errors');
                $view = 'Error';
            }
            $this->setAttribute('ticket_id', $movieItem->getTicketId());

            // Might want to export to fe here.

            $this->setContainerPluginState();
        }
        catch(Exception $e)
        {
            $errors[] = $e->getMessage();
            $view = 'Error';
        }

        $this->setAttribute('errors', $errors);

        return $view;
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