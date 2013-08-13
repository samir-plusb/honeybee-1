<?php

namespace Honeybee\Agavi\Validator;

use Honeybee\Core\Dat0r\RelationManager;
use Honeybee\Core\Dat0r\Module;
use Dat0r\Core\Document;
use Dat0r\Core\Error;
use Dat0r\Core\Field\ReferenceField;

class DocumentValidator extends \AgaviValidator
{
    protected function validate()
    {
        $tranlsationManager = $this->getContext()->getTranslationManager();

        $data = $this->getData($this->getArgument());
//error_log(print_r($data, true));exit;
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
                $document->checkMandatoryFields();
            }
            catch(Document\InvalidValueException $error)
            {
                $domain = sprintf('%s.list', $this->getModule()->getOption('prefix'));
                $this->setParameter('fieldname', $tranlsationManager->_($error->getFieldname(), $domain));
                $this->setParameter('value', $error->getValue());
                $this->throwError('invalid_values');

                $document = NULL;
                $success = FALSE;
            }
            catch(Document\MandatoryValueMissingException $error)
            {
                $domain = sprintf('%s.list', $this->getModule()->getOption('prefix'));
                $this->setParameter('fieldname', $tranlsationManager->_($error->getFieldname(), $domain));
                $this->throwError('missing_mandatory_values');

                $document = NULL;
                $success = FALSE;
            }
            catch(\Exception $error)
            {
                $document = NULL;
                $success = FALSE;
                $this->throwError();
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
                $document->setValues($data);
            }
        }

        return $document ? $document : $module->createDocument($data);
    }

    protected function getModule()
    {
        $module = $this->getContext()->getRequest()->getAttribute('module', 'org.honeybee.env');

        if (! ($module instanceof Module))
        {
            throw new \Exception(
                "Unable to determine honebee-module for the current action's scope." . PHP_EOL . 
                "Make sure that the Honeybee\Agavi\Routing\ModuleRoutingCallback is executed for the related route."
            );
        }

        return $module;
    }
}
