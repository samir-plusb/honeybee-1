<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;
use Honeybee\Core\Import\Filter;
use Dat0r\Core\Document as Dat0r;

class CustomDateFilter extends BaseFilter
{
    public function execute(BaseDocument $document)
    {
        $filter_output = array();

        $property_map = array("customDate" => "customDate");
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

            if($fieldname == 'customDate' && $prop_value == ''){
                $publishDate = $document_data['meta']['publishedAt'];
                $publishDate = explode('T', $publishDate)[0];
                $prop_value = $publishDate;
                error_log('normalizing shortId ' . $module->getField('shortId') . ' with value: ' . $prop_value);
            }
            $value = $prop_value;

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

}
