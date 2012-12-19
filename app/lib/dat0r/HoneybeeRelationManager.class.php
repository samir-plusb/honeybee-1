<?php

use Dat0r\Core\Runtime\Field\ReferenceField;
use Dat0r\Core\Runtime\Document\InvalidValueException;
use Dat0r\Core\Runtime\Document\DocumentSet;

class HoneybeeRelationManager
{
    public static function loadReferences(HoneybeeModule $module, array $data)
    {
        $referencedDocuments = array();

        foreach ($module->getFields() as $field)
        {
            $fieldname = $field->getName();
            
            if ($field instanceof ReferenceField && isset($data[$fieldname]))
            {
                $refData = $data[$fieldname];

                if (! is_array($refData))
                {
                    $error = new InvalidValueException(
                        sprintf("Unable to load reference for field %s", $fieldname)
                    );
                    $error->setFieldname($fieldname);

                    throw $error;
                }

                // @todo dont forget to adjust when introducing multi-reference fields.
                $referencedModules = $field->getReferencedModules();
                $referencedModule = $referencedModules[0];
                $referencedDocuments[$fieldname] = new DocumentSet();

                foreach ($refData as $identifier)
                {
                    $identifier = trim($identifier);
                    if (! empty($identifier) && 
                        ($referencedDocument = $referencedModule->getService()->get($identifier)))
                    {
                        $referencedDocuments[$fieldname]->add($referencedDocument);
                    }
                }
            }
        }

        return $referencedDocuments;
    }
}
