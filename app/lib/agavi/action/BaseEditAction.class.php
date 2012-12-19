<?php

class BaseEditAction extends ProjectBaseAction
{
    public function executeRead(AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();
        $document = $requestData->getParameter('document', $module->createDocument());

        $this->setAttribute('module', $module);
        $this->setAttribute('document', $document);

        return 'Input';
    }

    public function executeWrite(AgaviRequestDataHolder $requestData)
    {
        $view = 'Success';
        
        $module = $this->getModule();
        $this->setAttribute('module', $module);

        $document = $requestData->getParameter('document');

        try
        {
            $module->getService()->save($document);
        }
        catch(Exception $e)
        {
            $this->setAttribute('errors', array($e->getMessage()));
            // @todo very detailed log and if in development then throw $e
            $view = 'Error';
        }

        return $view;
    }

    public function handleError(AgaviRequestDataHolder $requestData)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $name => $error)
        {
            $errors[] = implode(', ', $error['errors']) . ': ' . $error['message'];
        }

        $this->setAttribute('errors', $errors);

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
