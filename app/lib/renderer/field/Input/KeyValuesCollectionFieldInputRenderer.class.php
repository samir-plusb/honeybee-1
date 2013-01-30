<?php

use Honeybee\Core\Dat0r\Document;

class KeyValuesCollectionFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-key-values-list';
    }

    protected function getWidgetOptions(Document $document)
    {
        $fieldname = $this->getField()->getName();
        $attributes = $document->getValue($fieldname);
        $attributes = empty($attributes) ? array() : $attributes;

        return array(
            'autobind' => TRUE,
            'fieldname' => $this->generateInputName($document),
            'data' => $attributes
        );
    }

    protected function getTemplateName()
    {
        return 'KeyValuesCollectionField.tpl.twig';
    }
}
