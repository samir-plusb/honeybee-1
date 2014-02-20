<?php

namespace Honeybee\Core\Job;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\ModuleService;
use Honeybee\Core\Dat0r\RelationManager;

abstract class DocumentJob extends BaseJob
{
    protected $module_class;

    protected $document_identifier;

    protected function loadDocument()
    {
        RelationManager::setMaxRecursionDepth(1);

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
