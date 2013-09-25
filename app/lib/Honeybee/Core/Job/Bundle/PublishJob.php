<?php

namespace Honeybee\Core\Job\Bundle;

use Honeybee\Core\Job\DocumentJob;

class PublishJob extends DocumentJob
{
    protected $exports;

    protected $success_gate;

    protected $error_gate;

    protected function execute(array $parameters = array())
    {
        $document = $this->loadDocument();
        $module = $document->getModule();

        $workflow_ticket = $document->getWorkflowTicket()->first();
        $workflow_step = $workflow_ticket->getWorkflowStep();

        if ('publish' === $workflow_step) {
            $document_service = $module->getService();
            $export_service = $module->getService('export');
            foreach ($this->exports as $export_name) {
                try {
                    $export_service->publish($export_name, $document);
                    if ($this->success_gate) {
                        $workflow_manager = $module->getWorkflowManager();
                        $workflow_manager->executeWorkflowFor($document, $this->success_gate);
                    }
                } catch(\Exception $e) {
                    if ($this->error_gate) {
                        $workflow_manager = $module->getWorkflowManager();
                        $workflow_manager->executeWorkflowFor($document, $this->error_gate);
                    }
                }
                $document_service->save($document);
            }
        } else {
            throw new \Exception(
                sprintf(
                    "The document is in an unexpected workflow state: %s, expected is: %s",
                    $workflow_step,
                    'publish'
                )
            );
        }
    }
}
