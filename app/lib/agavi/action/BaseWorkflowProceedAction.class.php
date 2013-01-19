<?php

class BaseWorkflowProceedAction extends ProjectBaseAction
{
    public function executeWrite(AgaviParameterHolder $parameters)
    {
        try
        {
            $module = $this->getModule();
            $service = $module->getService();

            $resource = $parameters->getParameter('document');
            $gate = $parameters->getParameter('gate');

            $manager = $module->getWorkflowManager();
            $result = $manager->proceedWorkflow($resource, $gate, $this->getContainer());

            $errorStates = array(
                WorkflowPluginResult::STATE_ERROR,
                WorkflowPluginResult::STATE_NOT_ALLOWED
            );

            if (in_array($result->getState(), $errorStates))
            {
                return 'Error';
            }
        }
        catch (Exception $e)
        {
            $this->setAttribute(
                'content',
                'An unexpected workflow error occured while processing: ' . $e->getMessage()
            );
            
            $this->setAttribute('reason', $e->getCode());
            $this->setAttribute('errors', array($e->getMessage()));

            return 'Error';
        }

        return 'Success';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $err)
        {
            if (! empty($err['message']))
            {
                $errors[] = $err['message'];
            }
            else
            {
                $errors[] = "An unexpected (validation) error occured.";
            }
        }

        $this->setAttribute('reason', 'validation');
        $this->setAttribute(
            'content',
            'The following errors occured while processing input: ' . implode(', ', $errors)
        );

        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    public function isSecure()
    {
        return TRUE;
    }
}
