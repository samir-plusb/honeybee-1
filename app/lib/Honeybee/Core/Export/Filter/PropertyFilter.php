<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Runtime\Document as Dat0r;
use Dat0r\Core\Runtime\Field\ReferenceField;

class PropertyFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $filterOutput = array();

        $propertyMap = $this->getConfig()->get('properties');
        $module = $document->getModule();

        foreach ($propertyMap as $fieldname => $targetKey)
        {
            $field = $module->getField($fieldname);
            $propValue = $document->getValue($fieldname);

            $value = NULL;
            
            if ($field instanceof ReferenceField)
            {
                $value = array();
                foreach ($propValue as $refDocument)
                {
                    $value[] = array('id' => $refDocument->getShortIdentifier());
                }
            }
            else if ($propValue instanceof Dat0r\Document)
            {
                $value = $propValue->toArray();
            }
            else
            {
                $value = $propValue;
            }

            $filterOutput[$targetKey] = $value;
        }

        return $filterOutput;
    }
}
