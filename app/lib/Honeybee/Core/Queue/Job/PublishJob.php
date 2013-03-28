<?php

namespace Honeybee\Core\Queue\Job;

class PublishJob extends BaseJob
{
    protected $moduleClass;

    protected $documentId;

    protected function execute(array $parameters = array())
    {
        $document = $this->loadDocument();
        $module = $document->getModule();
        $service = $module->getService();

        $workflowStep = $document->getWorkflowTicket()->getWorkflowStep();

        if ('publish' === $workflowStep)
        {
            // @todo integrate the export/deployment component here
        
            $workflowManager = $module->getWorkflowManager();
            $workflowManager->executeWorkflowFor($document, 'promote');
        }
        else
        {
            throw new Exception(
                sprintf(
                    "The document is in an unexpected workflow state: %s, expected is: %s",
                    $workflowStep,
                    'publish'
                )
            );
        }
    }

    protected function loadDocument()
    {
        $module = $this->loadModule();
        $service = $module->getService();

        return $service->get($this->documentId);
    }

    protected function loadModule()
    {
        if (! class_exists($this->moduleClass))
        {
            throw new Exception(
                "Unable to load module: '" . $this->moduleClass . "', for PublishJob."
            );
        }

        $implementor = $this->moduleClass;

        return $implementor::getInstance();
    }
}
