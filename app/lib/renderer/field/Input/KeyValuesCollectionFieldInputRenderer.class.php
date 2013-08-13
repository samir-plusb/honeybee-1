<?php

use Dat0r\Core\Document\IDocument;

class KeyValuesCollectionFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(IDocument $document)
    {
        return 'widget-key-values-list';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $fieldname = $this->getField()->getName();
        $attributes = $document->getValue($fieldname);
        $attributes = empty($attributes) ? array() : $attributes;

        return array_merge($parentOptions, array(
            'fieldname' => $this->generateInputName($document),
            'data' => $attributes
        ));
    }

    protected function getTemplateName()
    {
        return 'KeyValuesCollectionField.tpl.twig';
    }
}
