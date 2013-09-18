<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\ModuleService;

abstract class DocumentJob extends BaseJob
{
    protected $module_class;

    protected $document_identifier;

    protected function loadDocument()
    {
        $module = $this->loadModule();
        $service = $module->getService();

        return $service->get($this->document_identifier);
    }

    protected function loadModule()
    {
        if (!class_exists($this->module_class)) {
            throw new Exception(
                "Unable to load module: '" . $this->module_class . "', for PublishJob."
            );
        }
        $implementor = $this->module_class;

        return $implementor::getInstance();
    }
}
