<?php

use Honeybee\Core\Dat0r\Document;

class LocationInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-location-widget';
    }

    protected function getWidgetOptions(Document $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $location = $document->getValue($this->getField()->getName());
        $routing = AgaviContext::getInstance()->getRouting();

        $widgetOptions = array(
            'localize_url' => urldecode(htmlspecialchars($routing->gen('common.service.localize'))),
            'location' => $location ? $location->toArray() : array(),
            'fieldname' => $this->generateInputName($document)
        );

        return array_merge($widgetOptions, $parentOptions);
    }

    protected function getTemplateName()
    {
        return 'PlainWidget.tpl.twig';
    }
}
