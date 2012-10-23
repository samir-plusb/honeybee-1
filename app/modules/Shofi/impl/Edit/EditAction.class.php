<?php

class Shofi_EditAction extends ShofiBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $ticket = $parameters->getParameter('ticket');
        $workflowService = ShofiWorkflowService::getInstance();

        if ($ticket)
        {
            $placeContainer = $workflowService->fetchWorkflowItemById(
                $ticket->getItem()
            );
            $this->setAttribute('ticket', $ticket);
        }
        else
        {
            $placeContainer = $workflowService->createWorkflowItem(array(
                'masterRecord' => array(
                    'location' => array()
                ),
                'coreItem' => array(
                    'location' => array()
                ),
                'detailItem' => array(),
                'salesItem' => array()
            ));
        }

        $this->setAttribute('item', $placeContainer);
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
        $transManager = $this->getContext()->getTranslationManager();

        $detailData = $parameters->getParameter('detailItem', array());
        $attributes = array();
        if (isset($detailData['attributes']))
        {
            foreach ($detailData['attributes'] as $key => $values)
            {
                $attributes[] = array('name' => $key, 'values' => $values);
            }
        }
        $detailData['attributes'] = $attributes;

        $openingTimes = array();
        if (isset($detailData['openingTimes']))
        {
            foreach ($detailData['openingTimes'] as $openingTime)
            {
                $openingTimes[] = $openingTime;
            }
        }
        $detailData['openingTimes'] = $openingTimes;

        try
        {
            $ticket = $parameters->getParameter('ticket');
            $workflowService = ShofiWorkflowService::getInstance();

            if (! $ticket)
            {
                $placeContainer = $workflowService->createWorkflowItem(array(
                    'masterRecord' => $parameters->getParameter('coreItem', array(
                        'location' => array()
                    )),
                    'coreItem' => $parameters->getParameter('coreItem', array(
                        'location' => array()
                    )),
                    'detailItem' => $detailData,
                    'salesItem' => $parameters->getParameter('salesItem', array())
                ));
            }
            else
            {   
                $placeContainer = $workflowService->fetchWorkflowItemById(
                    $ticket->getItem()
                );
                if ($parameters->hasParameter('coreItem'))
                {
                    $coreItem = $parameters->getParameter('coreItem');
                    $placeContainer->updateCoreItem($coreItem);
                }
                if ($parameters->hasParameter('detailItem'))
                {
                    $placeContainer->updateDetailItem($detailData);
                }
                if ($parameters->hasParameter('salesItem'))
                {
                    $salesItem = $parameters->getParameter('salesItem');
                    $placeContainer->updateSalesItem($salesItem);
                }
            }

            if ($parameters->hasParameter('attributes'))
            {
                $placeContainer->setAttributes($parameters->getParameter('attributes'));
            }

            if (! $workflowService->storeWorkflowItem($placeContainer))
            {
                $errors[] = $transManager->_('storage_error', 'shofi.errors');
                $view = 'Error';
            }
            $this->setAttribute('ticket_id', $placeContainer->getTicketId());

            $location = $placeContainer->getCoreItem()->getLocation();
            if ($location)
            {
                $coords = $location->getCoordinates();
                if (! isset($coords['lon']) || ! isset($coords['lat']) || 0 == $coords['lon'] || 0 == $coords['lat'])
                {
                    $errors[] = $transManager->_('geo_invalid', 'shofi.errors');
                }
            }

            // Check if contentmachine export is enabled and all required settings are.
            $exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
            if ($exportAllowed)
            {
                $cmExport = new ContentMachineHttpExport(
                    AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
                );
                if (! $cmExport->exportShofiPlace($placeContainer))
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

        // @todo propagte events here, as we dont want add all "onsave" stuff inline.
        // even more find a better place than the action for doing this 'update/create' logic
        $keywords = $placeContainer->getDetailItem()->getKeywords();
        if (in_array('Kino', $keywords))
        {
            $frontendExport = new MoviesFrontendExport();
            $frontendExport->exportTheater($placeContainer);
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