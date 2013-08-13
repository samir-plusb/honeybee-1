<?php

use Dat0r\Core\Document\IDocument;

class TextCollectionFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(IDocument $document)
    {
        return 'widget-tags-list';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $fieldname = $this->getField()->getName();
        $texts = $document->getValue($fieldname);
        $texts = is_array($texts) ? $texts : array();

        $tags = array();

        foreach ($texts as $text)
        {
            $tags[] = array(
                'label' => $text,
                'value' => $text
            );
        }

        return array_merge($parentOptions, array(
            'fieldname' => $this->generateInputName($document),
            'max' => 0,
            'tags' => $tags,
            'tpl' => 'Stack'
        ));
    }
}
