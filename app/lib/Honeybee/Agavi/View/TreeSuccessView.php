<?php

namespace Honeybee\Agavi\View;

class TreeSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $layout = $this->hasAttribute('referenceField') ? 'reference' : NULL;
        $this->setupHtml($parameters, $layout);

        $module = $this->getAttribute('module');
        $tm = $this->getContext()->getTranslationManager();
        $this->setAttribute('_title', $tm->_($module->getName(), 'modules.labels') . ': ' . $tm->_('Tree view', 'modules.labels') . ' - ' . $tm->_('brand-name', 'modules.labels'));

        $this->getLayer('content')->setSlot(
            'tree',
            $this->createSlotContainer('Common', 'Tree', array(
                'tree' => $this->getAttribute('tree'),
                'config' => $this->getAttribute('config'),
            ), NULL, 'read')
        );

        if (! $this->hasAttribute('referenceField'))
        {
            $this->setBreadcrumb();
        }
    }

    protected function setBreadcrumb()
    {
        $module = $this->getAttribute('module');

        $treeRouteName = sprintf('%s.list', $module->getOption('prefix'));
        $routing = $this->getContext()->getRouting();

        $tm = $this->getContext()->getTranslationManager();
        $moduleName = $tm->_($module->getName(), 'modules.labels');
        $moduleCrumb = array(
            'text' => $moduleName,
            'link' => $routing->gen($treeRouteName, array(), array('omit_defaults' => TRUE)),
            'info' => $moduleName . ' - ' . $tm->_('Tree view', 'modules.labels'),
            'icon' => 'hb-icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'honeybee.breadcrumbs', array());
        if (1 <= count($breadcrumbs))
        {
            array_splice($breadcrumbs, 1);
        }

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }

    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $this->getResponse()->setContent(json_encode(
            array(
                'state' => 'ok',
                'data' => $this->getAttribute('tree')->toArray()
            )
        ));
    }
}
