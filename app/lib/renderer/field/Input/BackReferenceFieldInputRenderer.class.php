<?php

use Dat0r\Core\Field\ReferenceField;
use Dat0r\Core\Field\AggregateField;
use Dat0r\Core\Document\IDocument;

class BackReferenceFieldInputRenderer extends FieldInputRenderer
{
    protected function getTemplateName()
    {
        return "BackReference.tpl.twig";
    }

    protected function getWidgetType(IDocument $document)
    {
        return parent::getWidgetType($document) ?: 'widget-back-reference';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        return array_merge($parentOptions, array(
            'fieldname' => $this->generateInputName($document),
            'field_id' => $this->generateInputId($document),
            'realname' => $this->getField()->getName(),
            'auto_complete' => [
                'uri' => htmlspecialchars_decode(
                    urldecode($this->getRouteLink(
                        sprintf('%s.list', $this->options['referenced_module']), array(
                        'filter' => array(sprintf('%s.id', $this->options['filter_field']) => $document->getIdentifier()),

                    )))
                ),
                'display_field' => $this->options['display_field'],
                'identity_field' => $this->options['identity_field']
            ])
        );
    }
}
