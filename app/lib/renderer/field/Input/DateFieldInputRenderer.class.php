<?php

use Honeybee\Core\Dat0r\Document;

class DateFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-date-picker';
    }

    protected function getWidgetOptions(Document $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $date = $document->getValue($this->getField()->getName());

        return array_merge($parentOptions, array(
            'date' => empty($date) ? '' : $date, 
            'format' => 'dd.mm.yyyy',
            'field_name' => $this->generateInputName($document),
            'field_id' => str_replace(array('[', ']'), '', $this->generateInputName($document))
        ));
    }
}
