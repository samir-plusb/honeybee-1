<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Field\IField;

class CombineFilter extends BaseFilter
{
    protected static $supportedFields = array(
        'Dat0r\\Core\\Runtime\\Field\\TextField',
        'Dat0r\\Core\\Runtime\\Field\\TextCollectionField'
    );

    public function execute(Document $document)
    {
        $filterOutput = array();

        $fieldnames = $this->getConfig()->get('fieldnames');
        $key = $this->getConfig()->get('key');
        $module = $document->getModule();
        $values = array();

        foreach ($fieldnames as $fieldname)
        {
            $field = $module->getField($fieldname);
            $propValue = $document->getValue($fieldname);

            if ($this->isFieldTypeSupported($field) && ! empty($propValue))
            {
                if (is_string($propValue))
                {
                    $values[] = $propValue;
                }
                else if(is_array($propValue))
                {
                    $values = array_merge($propValue, $values);
                }
            }
        }

        $filterOutput[$key] = $values;

        return $filterOutput;
    }

    protected function isFieldTypeSupported(IField $field)
    {
        $fieldType = get_class($field);

        return in_array($fieldType, self::$supportedFields);
    }
}
