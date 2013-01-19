<?php

class BaseEditAction extends ProjectBaseAction
{
    public function executeRead(AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();
        $document = $requestData->getParameter('document', $module->createDocument());

        $this->setAttribute('module', $module);
        $this->setAttribute('document', $document);

        $this->setContainerPluginState();

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

        $this->setContainerPluginState();

        return $view;
    }

    protected function setContainerPluginState()
    {
        $pluginResult = $this->getContainer()->getAttribute(
            WorkflowInteractivePlugin::ATTR_RESULT,
            WorkflowInteractivePlugin::NS_PLUGIN_ATTRIBUTES
        );
        
        if ($pluginResult)
        {
            $pluginResult->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
            $pluginResult->setMessage(
                "Processed: " . get_class($this) 
                .' - ' . ucfirst($this->getContext()->getRequest()->getMethod())
            );
        }
    }
}
