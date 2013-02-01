<?php

namespace Honeybee\Agavi\View;

class TreeSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $module = $this->getAttribute('module');
        $this->setAttribute('_title', sprintf('Honeybee - %s: Hierarchie', $module->getName()));

        $this->getLayer('content')->setSlot(
            'tree',
            $this->createSlotContainer('Common', 'Tree', array(
                'tree' => $this->getAttribute('tree'),
                'config' => $this->getAttribute('config'),
            ), NULL, 'read')
        );

        $this->setBreadcrumb();
    }

    protected function setBreadcrumb()
    {
        $module = $this->getAttribute('module');

        $treeRouteName = sprintf('%s.list', $module->getOption('prefix'));
        $routing = $this->getContext()->getRouting();

        $moduleCrumb = array(
            'text' => $module->getName(),
            'link' => $routing->gen($treeRouteName),
            'info' => sprintf('%s - Baumansicht', $module->getName()),
            'icon' => 'icon-list'
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
            $this->getAttribute('tree')->toArray()
        ));
    }
}
