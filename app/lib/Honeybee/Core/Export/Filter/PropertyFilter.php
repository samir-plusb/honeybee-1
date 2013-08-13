<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Document as Dat0r;
use Dat0r\Core\Field\ReferenceField;

class PropertyFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $filter_output = array();

        $property_map = $this->getConfig()->get('properties');
        $module = $document->getModule();

        foreach ($property_map as $fieldname => $key)
        {
            $export_key = $key;
            $field = $module->getField($fieldname);
            $prop_value = $document->getValue($fieldname);

            $value = NULL;

            if ($field instanceof ReferenceField)
            {
                $display_fields = array();
                if (is_array($key))
                {
                    $export_key = $key['export_key'];
                    $display_fields = $key['display_fields'];
                    if ($key['flatten'] && count($display_fields) > 0)
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
                        if ($key['flatten'])
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
                $value = array();
                $export_key = is_array($key) ? $key['export_key'] : $key;

                foreach ($prop_value as $aggregate)
                {
                    if (is_array($key))
                    {
                        $value[] = $aggregate->getValue($key['display_field']);
                    }
                    else
                    {
                        $value[] = $aggregate->toArray();
                    }
                }
            }
            else
            {
                $value = $prop_value;
            }
            $filter_output[$export_key] = $value;
        }

        return $filter_output;
    }
}
