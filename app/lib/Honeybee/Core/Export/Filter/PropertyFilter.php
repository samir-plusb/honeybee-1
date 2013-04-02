<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Runtime\Document as Dat0r;

class PropertyFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $output = array();

        $propertyMap = $this->getConfig()->get('properties');

        foreach ($propertyMap as $fieldname => $targetKey)
        {
            $propValue = $document->getValue($fieldname);
            $value = $propValue;
            
            if ($propValue instanceof Dat0r\Document)
            {
                $value = $propValue->toArray();
            }

            $output[$targetKey] = $value;
        }

        return $output;
    }
}
