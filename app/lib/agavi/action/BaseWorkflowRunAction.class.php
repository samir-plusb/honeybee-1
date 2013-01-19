<?php

class BaseWorkflowRunAction extends ProjectBaseAction
{
    public function execute(AgaviParameterHolder $parameters)
    {
        try
        {
            $module = $this->getModule();
            $service = $module->getService();

            $resource = $parameters->getParameter('document');

            $manager = $module->getWorkflowManager();
            $result = $manager->runWorkflow($resource, $this->getContainer());

            if ($result instanceof WorkflowInteractivePluginResult)
            {
                $this->setAttribute('content', $result->getResponse()->getContent());
            }
            else
            {
                $this->setAttribute('content', $result->getMessage());
            }
        }
        catch (Exception $e)
        {
            $this->setAttribute(
                'content',
                'An unexpected workflow error occured while processing: ' . $e->getMessage()
            );

            $this->setAttribute('reason', $e->getCode());

            return 'Error';
        }

        $errorStates = array(
            WorkflowPluginResult::STATE_ERROR,
            WorkflowPluginResult::STATE_NOT_ALLOWED
        );

        if (in_array($result->getState(), $errorStates))
        {
            return 'Error';
        }

        return 'Success';
    }
}
