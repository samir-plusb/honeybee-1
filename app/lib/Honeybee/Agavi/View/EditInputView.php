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
        $tm = $this->getContext()->getTranslationManager();
        $this->setAttribute('_title', $tm->_($module->getName(), 'modules.labels') . ' - ' . $tm->_('Edit view', 'modules.labels'));

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
        $tm = $this->getContext()->getTranslationManager();
        $module = $this->getAttribute('module');
        $moduleName = $tm->_($module->getName(), 'modules.labels');
        $moduleCrumb = array(
            'text' => $moduleName,
            'link' => $routing->gen($module->getOption('prefix') . '.list'),
            'info' => $moduleName . ' - ' . $tm->_('List view (start)', 'modules.labels'),
            'icon' => 'hb-icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'honeybee.breadcrumbs', array());
        foreach ($breadcrumbs as $crumb)
        {
            if ('hb-icon-pencil' === $crumb['icon'])
            {
                return;
            }
        }
        $breadcrumbs[] = array(
            'text' => $tm->_('Edit', 'modules.labels'),
            'info' => $tm->_('Editing:', 'modules.labels') . ' ' . $this->getAttribute('document')->getIdentifier(),
            'icon' => 'hb-icon-pencil'
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
