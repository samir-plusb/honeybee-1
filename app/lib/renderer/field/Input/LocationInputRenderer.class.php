<?php

use Dat0r\Core\Document\IDocument;

class LocationInputRenderer extends AggregateFieldInputRenderer
{
    protected function getWidgetType(IDocument $document)
    {
        return isset($this->options['widget_type']) ? $this->options['widget_type'] : 'widget-location-aggregate';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $routing = AgaviContext::getInstance()->getRouting();

        $maxCount = $this->getField()->getOption('max', 0);
        $renderSingleEntry = false;
        if ($maxCount === 1 && isset($this->options['default_aggregate']))
        {
            $renderSingleEntry = true;
        }

        $visible_coordinates = false;
        if (array_key_exists('visible_coordinates', $this->options) && $this->options['visible_coordinates'] === true) {
            $visible_coordinates = true;
        }

        $widgetOptions = array(
            'fieldname' => $this->generateInputName($document),
            'location_type' => $this->options['location_type'],
            'single_entry' => $renderSingleEntry,
            'reverse_geocode' => $this->getField()->getOption('reverse_geocode', false),
            'visible_coordinates' => $visible_coordinates,
            'localize_url' => urldecode(htmlspecialchars(
                $routing->gen('common.service.localize')
            ))
        );

        return array_merge(parent::getWidgetOptions($document), $widgetOptions);
    }
}
