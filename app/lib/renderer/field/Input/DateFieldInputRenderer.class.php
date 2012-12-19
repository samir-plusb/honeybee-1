<?php

class DateFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(HoneybeeDocument $document)
    {
        return 'widget-date-picker';
    }

    protected function getWidgetOptions(HoneybeeDocument $document)
    {
        $date = $document->getValue($this->getField()->getName());

        return array(
            'autobind' => TRUE,
            'date' => empty($date) ? '' : $date, 
            'format' => 'dd.mm.yyyy',
            'field_name' => $this->generateInputName($document),
            'field_id' => str_replace(array('[', ']'), '', $this->generateInputName($document))
        );
    }
}
