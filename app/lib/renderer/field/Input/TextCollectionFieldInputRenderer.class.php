<?php

use Honeybee\Core\Dat0r\Document;

class TextCollectionFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-tags-list';
    }

    protected function getWidgetOptions(Document $document)
    {
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

        return array(
            'autobind' => TRUE,
            'fieldname' => $this->generateInputName($document),
            'max' => 0,
            'tags' => $tags,
            'tpl' => 'Stack'
        );
    }
}
