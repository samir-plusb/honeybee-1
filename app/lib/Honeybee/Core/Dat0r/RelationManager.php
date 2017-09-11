<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Field\ReferenceField;
use Dat0r\Core\Field\AggregateField;
use Dat0r\Core\Document\InvalidValueException;
use Dat0r\Core\Document\DocumentSet;
use Dat0r\Core\Module\IModule;

class RelationManager
{
    private static $pool_references = false;

    private static $reference_pool;

    private static $max_recursion_depth = 0;

    private static $recursion_depth = 0;

    public static function startPooling()
    {
        self::$pool_references = true;
    }

    public static function stopPooling()
    {
        self::$pool_references = false;
    }

    public static function getRecursionDepth()
    {
        return self::$recursion_depth;
    }

    public static function getMaxRecursionDepth()
    {
        return self::$max_recursion_depth;
    }

    public static function setMaxRecursionDepth($max_recursion_depth)
    {
        self::$max_recursion_depth = $max_recursion_depth;
    }

    public static function setRecursionDepth($max_recursion_depth)
    {
        self::$max_recursion_depth = $max_recursion_depth;
    }

    public static function prePopulateReferences(IModule $module, array $documents_data)
    {
        self::$recursion_depth++;

        $mapped_reference_ids = array();
        foreach ($documents_data as $document_data) {
            $mapped_reference_ids = array_merge_recursive(
                $mapped_reference_ids,
                self::mapAllReferences($module, $document_data)
            );
        }

        foreach ($mapped_reference_ids as $referenced_module_prefix => $reference_ids) {
            $reference_ids = array_values(array_unique($reference_ids));
            $module_class = preg_replace_callback(
                '/_([a-z])/',
                function($chars)
                {
                    return strtoupper($chars[1]);
                },
                $referenced_module_prefix
            );
            $module_class = ucfirst($module_class);
            $module_implementor = sprintf('\\Honeybee\\Domain\\%1$s\\%1$sModule', $module_class);
            $referenced_module = $module_implementor::getInstance();

            if (!empty($reference_ids)) {
                $reference_data = $referenced_module->getService()->getMany($reference_ids, 1000, 0);
                foreach ($reference_data['documents'] as $referenced_document) {
                    self::$reference_pool[$referenced_document->getIdentifier()] = $referenced_document;
                }
            }
        }
        self::$recursion_depth--;
    }

    protected static function mapAllReferences(IModule $module, array $values)
    {
        $mapped_reference_data = array();
        foreach ($module->getFields() as $fieldname => $field) {
            if ($field instanceof ReferenceField && isset($values[$fieldname])) {
                $mapped_reference_data = array_merge_recursive(
                    $mapped_reference_data,
                    self::mapDataToModules($values[$fieldname])
                );
            } elseif ($field instanceof AggregateField && isset($values[$fieldname])) {
                foreach ($values[$fieldname] as $aggregate_data) {
                    if (!isset($aggregate_data['type'])) {
                        error_log(print_r($aggregate_data, true));
                        continue;
                    }
                    $document_type = '\\'.$aggregate_data['type'];
                    $module_type = substr_replace(
                        $document_type,
                        'Module',
                        strrpos($document_type, 'Document'),
                        strlen('Document')
                    );
                    $aggregate_module = $module_type::getInstance();
                    $aggregate_references = self::mapAllReferences($aggregate_module, $aggregate_data);
                    $mapped_reference_data = array_merge_recursive($mapped_reference_data, $aggregate_references);
                }
            }
        }
        return $mapped_reference_data;
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
