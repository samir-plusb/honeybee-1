<?php

namespace Honeybee\Core\Queue\Job;

class PublishJob extends DocumentJob
{
    protected function execute(array $parameters = array())
    {
        $document = $this->loadDocument();
        $module = $document->getModule();

        $workflow_ticket = $document->getWorkflowTicket()->first();
        $workflow_step = $workflow_ticket->getWorkflowStep();
        if ('publish' === $workflow_step) {
            // @todo handle export errors and set corresponding workflow state.
            $export_service = $module->getService('export');
            $export_service->export('pulq-fe', $document);

            // @todo promote should be configurable for each job, as you dont always want it.
            $workflow_manager = $module->getWorkflowManager();
            $workflow_manager->executeWorkflowFor($document, 'promote');
        } else {
            throw new Exception(
                sprintf(
                    "The document is in an unexpected workflow state: %s, expected is: %s",
                    $workflow_step,
                    'publish'
                )
            );
        }
    }
}
