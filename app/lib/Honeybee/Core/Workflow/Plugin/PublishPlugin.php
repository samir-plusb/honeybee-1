<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Plugin;
use Honeybee\Core\Job\Bundle\PublishJob;
use Honeybee\Core\Job\Queue\KestrelQueue;

class PublishPlugin extends BasePlugin
{
    protected function doProcess()
    {
        $result;
        try {
            if ($this->getParameter('async', false)) {
                $result = $this->exportAsync();
            } else {
                $result = $this->exportSync();
            }
            $this->logInfo(
                sprintf("Successfully published document: %s", $this->getResource()->getIdentifier())
            );
        } catch(\Exception $e) {
            $this->logError(
                sprintf(
                    "An error occured while publishing document: %s\nError: %s\n",
                    $this->getResource()->getIdentifier(),
                    $e->getMessage()
                )
            );

            $user = \AgaviContext::getInstance()->getUser();
            $user->setAttribute('last_errors', array($e->getMessage()), "honeybee.workflow.errors");

            $result = new Plugin\Result();
            $result->setState(Plugin\Result::STATE_OK);
            $result->setMessage($e->getMessage());
            $result->setGate($this->getParameter('error_gate', 'demote'));
        }
        $result->freeze();

        return $result;
    }

    protected function exportAsync()
    {
        $resource = $this->getResource();
        // make sure the document's current state is persisted before triggering a job.
        $resource->getModule()->getService()->save($resource);

        // @todo introduce jobqueue_name setting.
        $queue = new KestrelQueue('prio:1-default_queue');
        $job_parameters = array(
            'module_class' => get_class($resource->getModule()),
            'document_identifier' => $resource->getIdentifier(),
            'exports' => $this->getParameter('exports'),
            'success_gate' => $this->getParameter('success_gate', 'promote'),
            'error_gate' => $this->getParameter('error_gate', 'demote'),
            'execution_delay' => 1
        );
        $queue->push(new PublishJob($job_parameters));

        $result = new Plugin\Result();
        $result->setState(Plugin\Result::STATE_EXPECT_INPUT);

        return $result;
    }

    protected function exportSync()
    {
        $resource = $this->getResource();
        $module = $resource->getModule();

        $exports = $this->getParameter('exports');
        $exports = is_array($exports) ? $exports : array();
        $export_service = $module->getService('export');
        foreach ($exports as $export_name) {
            $export_service->publish($export_name, $resource);
        }

        $result = new Plugin\Result();
        $result->setState(Plugin\Result::STATE_OK);
        $result->setGate($this->getParameter('success_gate', 'promote'));

        return $result;
    }
}
