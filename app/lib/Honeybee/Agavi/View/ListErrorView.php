<?php

namespace Honeybee\Agavi\View;

class ListErrorView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $this->setBreadcrumb();
    }

    /**
     * Handle presentation logic for json.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $data = array('ok' => FALSE, 'errors' => $this->getErrorMessages());
        $this->getResponse()->setContent(json_encode($data));
    }

    protected function setBreadcrumb()
    {
        $module = $this->getAttribute('module');
        $listRouteName = sprintf('%s.list', $module->getOption('prefix'));
        $routing = $this->getContext()->getRouting();

        $moduleCrumb = array(
            'text' => $module->getName(),
            'link' => $routing->gen($listRouteName),
            'info' => sprintf('%s - Listenansicht (Anfang)', $module->getName()),
            'icon' => 'icon-list'
        );

        $breadcrumbs = array(array(
            'text' => 'Liste - Fehler',
            'link' => $routing->gen($listRouteName),
            'info' => 'Fehlerhafte parameter',
            'icon' => 'icon-thumbs-down'
        ));

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }
}
