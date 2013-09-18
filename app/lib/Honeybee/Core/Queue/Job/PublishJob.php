<?php

namespace Honeybee\Core\Queue\Job;

class PublishJob extends DocumentJob
{
    protected $exports;

    protected $success_gate;

    protected $error_gate;

    protected $execution_delay;

    protected function execute(array $parameters = array())
    {
        if ($this->execution_delay) {
            sleep($this->execution_delay);
        }

        $document = $this->loadDocument();
        $module = $document->getModule();

        $workflow_ticket = $document->getWorkflowTicket()->first();
        $workflow_step = $workflow_ticket->getWorkflowStep();

        if ('publish' === $workflow_step) {
            $export_service = $module->getService('export');
            foreach ($this->exports as $export_name) {
                try {
                    $export_service->publish($export_name, $document);
                } catch(\Exception $e) {
                    if ($this->error_gate) {
                        $workflow_manager = $module->getWorkflowManager();
                        $workflow_manager->executeWorkflowFor($document, $this->error_gate);
                    }
                }
            }

            if ($this->success_gate) {
                $workflow_manager = $module->getWorkflowManager();
                $workflow_manager->executeWorkflowFor($document, $this->success_gate);
            }
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
