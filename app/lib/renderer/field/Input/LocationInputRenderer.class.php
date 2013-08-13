<?php

use Dat0r\Core\Document\IDocument;

class LocationInputRenderer extends AggregateFieldInputRenderer
{
    protected function getWidgetType(IDocument $document)
    {
        return 'widget-location-aggregate';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $routing = AgaviContext::getInstance()->getRouting();

        $widgetOptions = array(
            'fieldname' => $this->generateInputName($document),
            'location_type' => $this->options['location_type'],
            'localize_url' => urldecode(htmlspecialchars(
                $routing->gen('common.service.localize')
            ))
        );

        return array_merge(parent::getWidgetOptions($document), $widgetOptions);
    }
}
