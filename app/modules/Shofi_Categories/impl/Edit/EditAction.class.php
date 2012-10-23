<?php

class Shofi_Categories_EditAction extends ShofiCategoriesBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $ticket = $parameters->getParameter('ticket');
        $workflowService = ShofiCategoriesWorkflowService::getInstance();

        if ($ticket)
        {
            $categoryContainer = $workflowService->fetchWorkflowItemById(
                $ticket->getItem()
            );
            $this->setAttribute('ticket', $ticket);
        }
        else
        {
            $categoryContainer = $workflowService->createWorkflowItem(array(
                'masterRecord' => array()
            ));
        }

        $this->setAttribute('item', $categoryContainer);
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
            $workflowService = ShofiCategoriesWorkflowService::getInstance();
            $categoryItem = NULL;
            if (! $ticket)
            {
                $categoryItem = $workflowService->createWorkflowItem(array(
                    'masterRecord' => $parameters->getParameter('category', array())
                ));
            }
            else
            {   
                $categoryItem = $workflowService->fetchWorkflowItemById(
                    $ticket->getItem()
                );
                $categoryItem->updateMasterRecord(
                    $parameters->getParameter('category', array())
                );
            }
            
            if ($parameters->hasParameter('attributes'))
            {
                $categoryItem->setAttributes(
                    $parameters->getParameter('attributes')
                );
            }

            if (! $workflowService->storeWorkflowItem($categoryItem))
            {
                $errors[] = $transManager->_('storage_error', 'shofi.errors');
                $view = 'Error';
            }
            $this->setAttribute('ticket_id', $categoryItem->getTicketId());

            // Check if contentmachine export is enabled and all required settings are.
            $exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
            if ($exportAllowed)
            {
                $cmExport = new ContentMachineHttpExport(
                    AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
                );
                if (! $cmExport->exportShofiCategory($categoryItem))
                {
                    if ($cmExport->hasErrors())
                    {
                        $errors = array_merge(
                            $errors,
                            $cmExport->getLastErrors()
                        );
                    }
                    else
                    {
                        $errors[] = $transManager->_('export_error', 'shofi.errors');
                    }
                }
            }

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