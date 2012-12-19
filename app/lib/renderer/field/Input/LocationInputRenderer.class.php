<?php

class LocationInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(HoneybeeDocument $document)
    {
        return 'widget-location-widget';
    }

    protected function getWidgetOptions(HoneybeeDocument $document)
    {
        $location = $document->getValue($this->getField()->getName());
        $routing = AgaviContext::getInstance()->getRouting();

        return array(
            'autobind' => TRUE,
            'localize_url' => urldecode(htmlspecialchars($routing->gen('common.service.localize'))),
            'location' => $location ? $location->toArray() : array(),
            'fieldname' => $this->generateInputName($document)
        );
    }

    protected function getTemplateName()
    {
        return 'PlainWidget.tpl.php';
    }
}
