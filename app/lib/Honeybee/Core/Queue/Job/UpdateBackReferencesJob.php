<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\ModuleService;

class UpdateBackReferencesJob extends BaseJob
{
    protected $module_class;

    protected $document_identifier;

    protected function execute(array $parameters = array())
    {
        $document = $this->loadDocument();
        $module_service = new ModuleService();

        $referencing_modules = array();
        foreach ($module_service->getModules() as $module)
        {
            $reference_fields = $module->getFields(
                array(),
                array('Dat0r\Core\Field\ReferenceField')
            );
            foreach ($reference_fields as $reference_field)
            {
                foreach ($reference_field->getOption('references') as $reference_options)
                {
                    if (isset($reference_options['index_fields']) && $reference_options['module'] === '\\' . get_class($document->getModule()))
                    {
                        $referencing_modules[] = $module;
                    }
                }
            }
        }

        foreach ($referencing_modules as $referencing_module)
        {
            $service = $referencing_module->getService();
            $search_result = $service->find(array(
                'filter' => array('categories.id' => $document->getIdentifier()),
            ), 0, 100);
            foreach ($search_result['documents'] as $referencing_document)
            {
                if ($document->getIdentifier() === $referencing_document->getIdentifier())
                {
                    // prevent recursion for self references
                    continue;
                }
                error_log(
                    sprintf(
                        "[%s] Updated %s",
                        __CLASS__,
                        $referencing_document->getIdentifier()
                    )
                );
                $service->save($referencing_document);
            }
        }

        sleep(10);
    }

    protected function loadDocument()
    {
        $module = $this->loadModule();
        $service = $module->getService();

        return $service->get($this->document_identifier);
    }

    protected function loadModule()
    {
        if (! class_exists($this->module_class))
        {
            throw new Exception(
                "Unable to load module: '" . $this->module_class . "', for PublishJob."
            );
        }

        $implementor = $this->module_class;

        return $implementor::getInstance();
    }
}
