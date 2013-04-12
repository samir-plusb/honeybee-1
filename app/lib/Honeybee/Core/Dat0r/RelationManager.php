<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Runtime\Field\ReferenceField;
use Dat0r\Core\Runtime\Document\InvalidValueException;
use Dat0r\Core\Runtime\Document\DocumentSet;

class RelationManager
{
    private static $referencePool;

    private static $referenceDepth = 0;

    public static function loadReferences(Document $document, array $data)
    {
        $referencedDocuments = array();

        if (0 === self::$referenceDepth)
        {
            self::$referencePool = array();
        }

        self::$referenceDepth++;
        self::$referencePool[$document->getIdentifier()] = $document;

        $referenceFields = $document->getModule()->getFields(
            array(), array('Dat0r\Core\Runtime\Field\ReferenceField')
        );

        foreach ($referenceFields as $referenceField)
        {
            $fieldname = $referenceField->getName();
            if (! isset($data[$fieldname]))
            {
                continue;
            }

            $fieldData = $data[$fieldname];
            if (! is_array($fieldData))
            {
                $error = new InvalidValueException(
                    sprintf("Unable to load reference for field %s", $fieldname)
                );
                $error->setFieldname($fieldname);

                throw $error;
            }

            $referencedDocuments[$fieldname] = self::getReferenceDocuments($referenceField, $fieldData);
        }

        self::$referenceDepth--;

        return $referencedDocuments;
    }

    protected static function getReferenceDocuments(ReferenceField $field, array $fieldData)
    {
        self::loadFieldReferences($field, $fieldData);

        $referencedDocuments = new DocumentSet();
        foreach ($fieldData as $reference)
        {
            if (isset($reference['id']) && isset(self::$referencePool[$reference['id']]))
            {
                $referencedDocuments->add(self::$referencePool[$reference['id']]);
            }
            else
            {
                // throw execpetion?
            }
        }

        return $referencedDocuments;
    }

    protected static function loadFieldReferences(ReferenceField $field, array $fieldData)
    {
        $mappedRefData = self::mapDataToModules($fieldData);

        foreach ($field->getReferencedModules() as $referencedModule)
        {
            $modulePrefix = $referencedModule->getOption('prefix');
            if (! isset($mappedRefData[$modulePrefix]))
            {
                continue;
            }

            $pooledDocuments = self::$referencePool;
            $idsToLoad = array_filter($mappedRefData[$modulePrefix], function($documentIdentifier) use ($pooledDocuments)
            {
                return ! isset($pooledDocuments[$documentIdentifier]);
            });

            if (! empty($idsToLoad))
            {
                sort($idsToLoad); // remove array-filter key artifacts o0
                $referenceData = $referencedModule->getService()->getMany($idsToLoad);

                foreach ($referenceData['documents'] as $referencedDocument)
                {
                    self::$referencePool[$referencedDocument->getIdentifier()] = $referencedDocument;
                }
            }
        }
    }

    protected static function mapDataToModules(array $fieldData)
    {
        $mappedData = array();

        foreach ($fieldData as $reference)
        {
            if (! isset($reference['module']))
            {
                continue;
            }

            if (! isset($mappedData[$reference['module']]))
            {
                $mappedData[$reference['module']] = array();
            }

            $mappedData[$reference['module']][] = $reference['id'];
        }

        return $mappedData;
    }
}
