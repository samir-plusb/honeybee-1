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

        $maxCount = $this->getField()->getOption('max', 0);
        $renderSingleEntry = false;
        if ($maxCount === 1 && isset($this->options['default_aggregate']))
        {
            $renderSingleEntry = true;
        }
        $widgetOptions = array(
            'fieldname' => $this->generateInputName($document),
            'location_type' => $this->options['location_type'],
            'single_entry' => $renderSingleEntry,
            'localize_url' => urldecode(htmlspecialchars(
                $routing->gen('common.service.localize')
            ))
        );

        return array_merge(parent::getWidgetOptions($document), $widgetOptions);
    }
}
