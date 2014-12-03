<?php

use Dat0r\Core\Document\IDocument;

class DateFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(IDocument $document)
    {
        return 'widget-date-picker';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $tm = $this->getTranslationManager();
        $td = $this->getTranslationDomain($document);
        $fieldName = $this->getField()->getName();

        $placeholder = $tm->_($fieldName . '_placeholder', $td);
        if ($placeholder === $fieldName . '_placeholder') {
            $placeholder = null;
        }

        $date = $document->getValue($fieldName);
        return array_merge($parentOptions, array(
            'date' => empty($date) ? '' : $date,
            'format' => isset($this->options['format']) ? $this->options['format'] : 'dd.MM.yyyy hh:mm:ss',
            'field_name' => $this->generateInputName($document),
            'time_only' => isset($this->options['time_only']) ? (bool)$this->options['time_only'] : false,
            'placeholder' => $placeholder
        ));
    }
}
