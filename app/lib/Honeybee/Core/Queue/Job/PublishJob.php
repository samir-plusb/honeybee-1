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

        // @todo integrate the export/deployment component here
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
