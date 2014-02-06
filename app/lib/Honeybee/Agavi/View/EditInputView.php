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
        $this->setupHtml($parameters, $parameters->getParameter('layout', null));

        $module = $this->getAttribute('module');
        $tm = $this->getContext()->getTranslationManager();

        $this->setAttribute('_title', $tm->_($module->getName(), 'modules.labels') . ' - ' . $tm->_('Edit view', 'modules.labels'));

        $this->setBreadcrumb();

        $document = $this->getAttribute('document');
        $renderer = new DocumentInputRenderer($module);
        $form = $renderer->render($document);

        $list_setting_name = sprintf('%s_last_list_url', $module->getOption('prefix'));
        $last_list_url = $this->getContext()->getUser()->getAttribute($list_setting_name, 'honeybee.list', false);
        if ($last_list_url) {
            $this->setAttribute('list_url', $last_list_url);
        }

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

        $breadcrumbs = array();
        $breadcrumbs[] = $moduleCrumb;
        $breadcrumbs[] = array(
            'text' => $tm->_('Edit', 'modules.labels'),
            'info' => $tm->_('Editing:', 'modules.labels') . ' ' . $this->getAttribute('document')->getIdentifier(),
            'icon' => 'hb-icon-pencil'
        );

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }
}
