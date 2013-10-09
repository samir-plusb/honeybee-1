<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Field\ReferenceField;
use Dat0r\Core\Document\InvalidValueException;
use Dat0r\Core\Document\DocumentSet;

class RelationManager
{
    private static $pool_references = false;

    private static $reference_pool;

    private static $recursion_depth = 0;

    public static function startPooling()
    {
        self::$pool_references = true;
    }

    public static function stopPooling()
    {
        self::$pool_references = false;
    }

    public static function loadReferences(BaseDocument $document, array $data)
    {
        if (self::$recursion_depth === 0 && !self::$pool_references) {
            self::$reference_pool = array();
        }
        self::$recursion_depth++;

        if ($document instanceof Document) {
            self::$reference_pool[$document->getIdentifier()] = $document;
        }

        $referenced_documents = array();
        $reference_fields = $document->getModule()->getFields(
            array(),
            array('Dat0r\Core\Field\ReferenceField')
        );
        foreach ($reference_fields as $reference_field) {
            $fieldname = $reference_field->getName();
            if (!isset($data[$fieldname])) {
                continue;
            }

            $field_data = $data[$fieldname];
            if (!is_array($field_data)) {
                $error = new InvalidValueException(
                    sprintf("Unable to load reference for field %s", $fieldname)
                );
                $error->setFieldname($fieldname);
                throw $error;
            }
            $referenced_documents[$fieldname] = self::getReferenceDocuments($reference_field, $field_data);
        }

        self::$recursion_depth--;

        return $referenced_documents;
    }

    protected static function getReferenceDocuments(ReferenceField $field, array $field_data)
    {
        self::loadFieldReferences($field, $field_data);

        $referenced_documents = new DocumentSet();
        foreach ($field_data as $reference) {
            if (isset($reference['id']) && isset(self::$reference_pool[$reference['id']])) {
                $referenced_documents->add(self::$reference_pool[$reference['id']]);
            } else {
                // throw execpetion?
            }
        }

        return $referenced_documents;
    }

    protected static function loadFieldReferences(ReferenceField $field, array $field_data)
    {
        $mapped_reference_data = self::mapDataToModules($field_data);

        foreach ($field->getReferencedModules() as $referenced_module) {
            $module_prefix = $referenced_module->getOption('prefix');
            if (!isset($mapped_reference_data[$module_prefix])) {
                continue;
            }

            $pooled_documents = self::$reference_pool;
            $ids_to_load = array_filter(
                $mapped_reference_data[$module_prefix],
                function($document_identifier) use ($pooled_documents)
                {
                    return !isset($pooled_documents[$document_identifier]);
                }
            );

            if (!empty($ids_to_load)) {
                sort($ids_to_load); // remove array-filter key artifacts o0
                $reference_data = $referenced_module->getService()->getMany($ids_to_load);
                foreach ($reference_data['documents'] as $referenced_document) {
                    self::$reference_pool[$referenced_document->getIdentifier()] = $referenced_document;
                }
            }
        }
    }

    protected static function mapDataToModules(array $field_data)
    {
        $mapped_data = array();
        foreach ($field_data as $reference) {
            if (!isset($reference['module'])) {
                continue;
            }
            if (!isset($mapped_data[$reference['module']])){
                $mapped_data[$reference['module']] = array();
            }
            $mapped_data[$reference['module']][] = $reference['id'];
        }

        return $mapped_data;
    }
}
