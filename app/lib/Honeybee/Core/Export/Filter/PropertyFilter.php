<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;
use Honeybee\Core\Import\Filter;
use Dat0r\Core\Document as Dat0r;
use Dat0r\Core\Field\ReferenceField;

class PropertyFilter extends BaseFilter
{
    public function execute(BaseDocument $document)
    {
        $filter_output = array();

        $property_map = $this->getConfig()->get('properties');
        $module = $document->getModule();
        $document_data = $document->toArray();

        foreach ($property_map as $source_key => $key)
        {
            $fieldname = $source_key;
            if (preg_match('~([\w-_]+)(\[.+\])+~is', $source_key, $matches))
            {
                $fieldname = $matches[1];
            }

            $export_key = $key;
            $cast_to = false;
            if (is_array($key))
            {
                $export_key = $key['export_key'];
                $cast_to = isset($key['cast_to']) ? $key['cast_to'] : false;
            }
            $field = $module->getField($fieldname);
            $prop_value = $document->getValue($fieldname);

            $value = NULL;

            if ($field instanceof ReferenceField)
            {
                $flatten = false;
                $display_fields = array();
                if (is_array($key))
                {
                    $export_key = $key['export_key'];
                    $display_fields = $key['display_fields'];
                    $flatten = isset($key['flatten']) && true === $key['flatten'];
                    if ($flatten && count($display_fields) > 0)
                    {
                        $display_fields = array($key['display_fields'][0]);
                    }
                }

                $value = array();
                foreach ($prop_value as $ref_document)
                {
                    if (empty($display_fields))
                    {
                        $value[] = array('id' => $ref_document->getShortIdentifier());
                    }
                    else
                    {
                        if ($flatten)
                        {
                             $value[] = $ref_document->getValue($display_fields[0]);
                        }
                        else
                        {
                            $next_value = array();
                            foreach ($display_fields as $display_field)
                            {
                                $next_value[$display_field] = $ref_document->getValue($display_field);
                            }
                            $value[] = $next_value;
                        }
                    }
                }
            }
            else if ($prop_value instanceof Dat0r\DocumentCollection)
            {
                $value = Filter\RemapFilter::getArrayValue($document_data, $source_key);
            }
            else
            {
                $value = $prop_value;
            }

            $array_key = preg_replace('~\[\d+\]\[\w+\]$~is', '', $export_key);
            if ($array_key !== $export_key)
            {
                $parent_array = Filter\RemapFilter::getArrayValue($filter_output, $array_key);
                if (empty($value) && empty($parent_array))
                {
                    $value = array();
                    $export_key = $array_key;
                }
                else if (empty($value))
                {
                    continue;
                }
            }

            if (is_scalar($value))
            {
                $value = trim($value);
                if ($cast_to)
                {
                    switch ($cast_to)
                    {
                        case 'string':
                            $value = (string)$value;
                            break;
                        case 'int':
                            $value = (int)$value;
                            break;
                        case 'bool':
                        case 'boolean':
                            $value = (bool)$value;
                            break;
                        case 'float':
                            $value = (float)$value;
                            break;
                    }
                }
            }
            Filter\RemapFilter::setArrayValue($filter_output, $export_key, $value);
        }

        return $filter_output;
    }

    static public function filterEmptyValues($item)
    {
        if (is_array($item))
        {
            return array_filter($item, array(__CLASS__, 'filterEmptyValues'));
        }

        return !empty($item);
    }
}
