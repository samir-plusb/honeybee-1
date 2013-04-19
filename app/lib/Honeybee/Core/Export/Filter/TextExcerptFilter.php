<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Runtime\Document as Dat0r;
use Dat0r\Core\Runtime\Field\ReferenceField;

class TextExcerptFilter extends BaseFilter
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
            $filterOutput[$targetKey] = $this->createExcerptFor($propValue);
        }

        return $filterOutput;
    }

    public function createExcerptFor($value)
    {
        $words = $this->getConfig()->get('words', 30);;
        $skip = $this->getConfig()->get('skip', 0);;
        $ellipsis = $this->getConfig()->get('ellipsis', '…');
        $strip_tags = $this->getConfig()->get('strip_tags', true);

        if (is_array($value))
        {
            $value = implode(', ', $value);
            if ($strip_tags)
            {
                $value = strip_tags($value);
            }
        }
        else
        {
            if ($strip_tags)
            {
                $value = strip_tags($value);
            }

            return implode(' ', array_slice(explode(' ', $value), $skip, $words)) . $ellipsis;
        }

        return $value;
    }
}
