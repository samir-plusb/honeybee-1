<?php

use Dat0r\Core\Runtime\Document\InvalidValueException;
use Dat0r\Core\Runtime\Field\ReferenceField;

class HoneybeeDocumentValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());
        $module = $this->getModule();
        $service = $module->getService();
        $document = NULL;
        $success = TRUE;

        if (is_array($data))
        {
            try
            {
                if (! $document = $this->loadDocument($data))
                {
                    $success = FALSE;
                    $this->throwError('non_existant');
                }
            }
            catch(InvalidValueException $error)
            {
                $document = NULL;
                $success = FALSE;
                $this->throwError('invalid_values', $error->getFieldname());
            }
        }
        else if (! ($document = $service->get($data)))
        {

            $success = FALSE;
            $this->throwError('non_existant');
        }

        if ($success && $document)
        {
            $this->export($document, $this->getParameter('export', 'document'));
        }

        return $success;
    }

    protected function loadDocument(array $data)
    {
        $document = NULL;
        $module = $this->getModule();
        $service = $module->getService();

        $identifier = NULL;
        if (isset($data['identifier']))
        {
            $identifier = trim($data['identifier']);
        }
        else if(($id = $this->getData('id')))
        {
           $identifier = trim($id);
        }

        if ($identifier)
        {
            if ($document = $service->get($identifier))
            {
                $references = HoneybeeRelationManager::loadReferences($module, $data);
                $data = array_merge($data, $references);
                $document->setValues($data);
            }
        }
        else
        {
            $document = $module->createDocument($data);
        }

        return $document;
    }

    protected function getModule()
    {
        $module = $this->getContext()->getRequest()->getAttribute('module', 'org.honeybee.env');

        if (! ($module instanceof HoneybeeModule))
        {
            throw new Exception(
                "Unable to determine honebee-module for the current action's scope." . PHP_EOL . 
                "Make sure that the HoneybeeModuleRoutingCallback is executed for the related route."
            );
        }

        return $module;
    }
}
