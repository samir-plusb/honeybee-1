<?php

namespace Honeybee\Agavi\View;

use Honeybee\Core\Dat0r\Document;
use DocumentInputRenderer;

class EditInputView extends BaseView
{
    /**
     * Run this view for the html output type.
     */
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $module = $this->getAttribute('module');
        $this->setAttribute('_title', $module->getName() . ' - Edit');

        $document = $this->getAttribute('document');
        $data = $document->toArray();
        $this->setAttribute('document_data', $data);

        $widgetData = $this->getWidgets($document);
        $this->registerJsWidgetOptions($widgetData['options']);
        $this->registerClientSideController($widgetData['registration']);

        $this->setBreadcrumb();

        $renderer = new DocumentInputRenderer($module);
        $form = $renderer->render($document);

        $this->setAttribute('form', $form);
    }

    protected function setBreadcrumb()
    {
        $routing = $this->getContext()->getRouting();
        $module = $this->getAttribute('module');
        $moduleCrumb = array(
            'text' => $this->getContext()->getTranslationManager()->_($module->getName(), 'modules.labels'),
            'link' => $routing->gen($module->getOption('prefix') . '.list'),
            'info' => $module->getName() . ' - Listenansicht (Anfang)',
            'icon' => 'icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'honeybee.breadcrumbs', array());
        foreach ($breadcrumbs as $crumb)
        {
            if ('icon-pencil' === $crumb['icon'])
            {
                return;
            }
        }
        $breadcrumbs[] = array(
            'text' => 'Bearbeiten',
            'info' => 'Bearbeitung von: ' . $this->getAttribute('document')->getIdentifier(),
            'icon' => 'icon-pencil'
        );
        
        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }

    /**
     * Register the given widgets to the client side controller.
     */
    protected function registerClientSideController(array $widgets = array())
    {
        $document = $this->getAttribute('document');

        $controllerOptions = array(
            'autobind' => TRUE, 
            'widgets' => $widgets,
            'identifier' => $document->getIdentifier(),
            'revision' => $document->getRevision()
        );

        $this->setAttribute(
            'controller_options',
            htmlspecialchars(json_encode($controllerOptions))
        );
    }

    protected function registerJsWidgetOptions(array $widgets = array())
    {
        foreach ($widgets as $attributeName => $widgetOptions)
        {
            $this->setAttribute(
                $attributeName,
                htmlspecialchars(json_encode($widgetOptions))
            );
        }
    }

    /**
     * register widgets by providing: name, type and selector
     * init widgets by providing options below a key you will use in your templates.
     */
    protected function getWidgets(Document $document)
    {
        return array(
            'options' => array(),
            'registration' => array()
        );
    }
}
