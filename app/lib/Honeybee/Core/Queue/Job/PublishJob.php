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

        $workflowStep = $document->getWorkflowTicket()->first()->getWorkflowStep();

        if ('publish' === $workflowStep)
        {
            // @todo handle export errors and set corresponding workflow state.
            $exportService = $module->getService('export');
            $exportService->export('pulq-fe', $document);

            // @todo promote should be configurable for each job, as you dont always want it.
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
