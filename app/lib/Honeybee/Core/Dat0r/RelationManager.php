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
        if (0 === self::$referenceDepth)
        {
            self::$referencePool = array();
        }

        self::$referenceDepth++;
        self::$referencePool[$document->getIdentifier()] = $document;

        $module = $document->getModule();
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

                if (! empty($refData))
                {
                    $pooledDocuments = self::$referencePool;
                    $idsToLoad = array_filter($refData, function($documentIdentifier) use ($pooledDocuments)
                    {
                        return ! isset($pooledDocuments[$documentIdentifier]);
                    });

                    foreach (array_diff($refData, $idsToLoad) as $pooledIdentifier)
                    {
                        $referencedDocuments[$fieldname]->add(self::$referencePool[$pooledIdentifier]);
                    }

                    if (! empty($idsToLoad))
                    {
                        sort($idsToLoad);
                        $referenceData = $referencedModule->getService()->getMany($idsToLoad);
                        foreach ($referenceData['documents'] as $referencedDocument)
                        {
                            self::$referencePool[$referencedDocument->getIdentifier()] = $referencedDocument;
                            $referencedDocuments[$fieldname]->add($referencedDocument);
                        }
                    }
                }
            }
        }

        self::$referenceDepth--;

        return $referencedDocuments;
    }
}
