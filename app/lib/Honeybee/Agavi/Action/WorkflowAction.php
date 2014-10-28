<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Workflow\Plugin;
use AgaviRequestDataHolder;
use AgaviConfig;
use Exception;

class WorkflowAction extends BaseAction
{
    public function executeWrite(AgaviRequestDataHolder $request_data)
    {
        $view = $this->executeRead($request_data);
        if ($request_data->hasParameter('gate')) {
            $this->getModule()->getService()->save(
                $request_data->getParameter('document')
            );
        }
        
        // The following setting allows to specify a delay (in seconds) for loading the page after 
        // a write operation.
        // 
        // This is to provide a better feeling to the user regarding the eventual consistency of
        // the application.
        // This delay should be set according to the speed of the River in syncing the Storage with the Finder.
        //
        // Use it carefully.
        // 
        // The attribute 'skip_after_write_delay' allows to skip the delay in situation where it is not needed
        // (like processing the workflow for many documents)
        //
        $reload_delay = AgaviConfig::get('core.after_write_delay', null);
        $skip_delay = $this->getAttribute('skip_after_write_delay', false);

        if(!$skip_delay && !is_null($reload_delay) && is_numeric($reload_delay)) {
            sleep($reload_delay);
        }

        return $view;
    }

    public function executeRead(AgaviRequestDataHolder $request_data)
    {
        try {
            $module = $this->getModule();
            $service = $module->getService();

            $resource = $request_data->getParameter('document');
            $gate = $request_data->getParameter('gate', null);

            $manager = $module->getWorkflowManager();
            $result = $manager->executeWorkflowFor($resource, $gate, $this->getContainer());
            $this->setAttribute('result', $result);

            if ($result instanceof Plugin\InteractionResult) {
                $this->setAttribute('content', $result->getResponse()->getContent());
            } else {
                $this->setAttribute('content', $result->getMessage());
            }

            $error_states = array(Plugin\Result::STATE_ERROR, Plugin\Result::STATE_NOT_ALLOWED);
            if (in_array($result->getState(), $error_states)) {
                return 'Error';
            }
        } catch (Exception $e) {
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

    public function handleError(AgaviRequestDataHolder $request_data)
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $err) {
            if (!empty($err['message'])) {
                $errors[] = $err['message'];
            } else {
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

    public function getCredentials()
    {
        return sprintf(
            '%s::%s',
            $this->getModule()->getOption('prefix'),
            $this->getContainer()->getRequestMethod()
        );
    }

    protected function executeWorkflow()
    {

    }
}
