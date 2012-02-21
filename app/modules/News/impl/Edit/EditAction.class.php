<?php

/**
 * The News_EditAction is repsonseable for loading our imported news for display.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_EditAction extends NewsBaseAction
{
    /**
     * Execute the read logic for this action, hence prompt for an asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setAttribute('ticket', $parameters->getParameter('ticket'));
        $this->setAttribute('list_pos', $parameters->getParameter('list_pos', 0));
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

    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $contentItemData = $parameters->getParameter('content_item');
        $ticket = $parameters->getParameter('ticket');
        $this->setAttribute('ticket', $parameters->getParameter('ticket'));
        $workflowItem = $ticket->getWorkflowItem();
        if (! $workflowItem->addContentItem($contentItemData))
        {
            $workflowItem->updateContentItem($contentItemData);
        }

        $supervisor = Workflow_SupervisorModel::getInstance();
        $supervisor->getItemPeer()->storeItem($workflowItem);

        $this->setContainerPluginState();
        return 'Success';
    }

    public function handleWriteError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $errors = $this->getContainer()->getValidationManager()->getErrors();
        $messages = "";
        foreach ($errors as $error)
        {
            $messages .= implode(PHP_EOL, $error['messages']);
        }
        $this->setAttribute('error_message', $messages);
        $this->setAttribute('ticket', $parameters->getParameter('ticket'));
        $this->setContainerPluginState();
        return 'Error';
    }

    public function handleReadError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $errors = $this->getContainer()->getValidationManager()->getErrors();
        $messages = "";
        foreach ($errors as $error)
        {
            $messages .= implode(PHP_EOL, $error['messages']);
        }
        $this->setAttribute('error_message', $messages);
        $this->setAttribute('ticket', $parameters->getParameter('ticket'));
        $this->setContainerPluginState();
        return 'Error';
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
