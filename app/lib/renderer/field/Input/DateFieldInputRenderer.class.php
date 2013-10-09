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
            'format' => 'dd.mm.yyyy',
            'field_name' => $this->generateInputName($document),
            'placeholder' => $placeholder
        ));
    }
}
