<?php

namespace Honeybee\Agavi\View;

use Dat0r\Core\Field\ReferenceField;
use Honeybee\Core\Storage\Memory\CsvStorage;
use Honeybee\Core\Storage\Memory\XmlStorage;

class ListSuccessView extends BaseView
{
    /**
     * Handle presentation logic for the web (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $layout = $this->hasAttribute('referenceField') ? 'reference' : NULL;
        $this->setupHtml($parameters, $layout);
        $module = $this->getAttribute('module');

        $tm = $this->getContext()->getTranslationManager();
        $this->setAttribute('_title', $tm->_($module->getName(), 'modules.labels') . ': ' . $tm->_('List view', 'modules.labels'));

        $this->getLayer('content')->setSlot(
            'list',
            $this->createSlotContainer('Common', 'List', array(
                'config' => $this->getAttribute('config'),
                'state' => $this->getAttribute('state')
            ), NULL, 'read')
        );

        $enableFolders =
            ! $this->hasAttribute('referenceField') &&
            (TRUE === \AgaviConfig::get(sprintf('%s.sidebar.folders.enabled', $module->getOption('prefix')), FALSE));

        if (TRUE === $enableFolders)
        {
            $sidebarTrees = $this->getSidebarTreeRelationData();
            if (! empty($sidebarTrees))
            {
                $sidebarParams = array('tree_relation_data' => $this->getSidebarTreeRelationData());
                $this->getLayer('content')->setSlot(
                    'sidebar',
                    $this->createSlotContainer('Common', 'Sidebar', $sidebarParams, NULL, 'read')
                );
            }
        }

        if (! $this->hasAttribute('referenceField'))
        {
            $this->setBreadcrumb();
        }
    }

    /**
     * Handle presentation logic for the web (json).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return $this->createSlotContainer('Common', 'List', array(
            'config' => $this->getAttribute('config'),
            'state' => $this->getAttribute('state')
        ), NULL, 'read');
    }

    public function executeXml(\AgaviRequestDataHolder $parameters)
    {
        $list_state = $this->getAttribute('state');
        $module = $this->getAttribute('module');

        $this->getResponse()->setHttpHeader(
            'Content-disposition',
            'attachment; filename='.$module->getName().'-List.xml'
        );

        $xml_export = $module->getService('export')->getExport('list-xml');
        $stream_id = $xml_export->getStorage()->getConfig()->get('write_to');

        $generate_xml = function() use ($list_state, $module, $xml_export) {
            // no need to check safe mode, as we require php >= 5.3 anyway
            set_time_limit(300);

            $document_service = $module->getService();
            $search_spec = array();
            if ($list_state->hasSearch()) {
                $search_spec['search'] = $list_state->getSearch();
            }
            if ($list_state->hasFilter()) {
                $search_spec['filter'] = $list_state->getFilter();
            }
            $document_service->walkDocuments($search_spec, 100, function($document) use ($xml_export)
            {
                $xml_export->publish($document);
            });

            $xml_resource = $xml_export->getStorage()->getResource();
            $xml_resource->endDocument();

            return $xml_resource;
        };

        if ($stream_id === XmlStorage::OUTPUT_STREAM) {
            return $generate_xml;
        }

        $xml_resource = $generate_xml();
        rewind($xml_resource);

        return $xml_resource;
    }

    public function executeCsv(\AgaviRequestDataHolder $parameters)
    {
        $list_state = $this->getAttribute('state');
        $module = $this->getAttribute('module');

        $this->getResponse()->setHttpHeader(
            'Content-disposition',
            'attachment; filename='.$module->getName().'-List.csv'
        );

        $csv_export = $module->getService('export')->getExport('list-csv');
        $stream_id = $csv_export->getStorage()->getConfig()->get('write_to');

        $generate_csv = function() use ($list_state, $module, $csv_export) {
            // no need to check safe mode, as we require php >= 5.3 anyway
            set_time_limit(300);

            $document_service = $module->getService();
            $search_spec = array();
            if ($list_state->hasSearch()) {
                $search_spec['search'] = $list_state->getSearch();
            }
            if ($list_state->hasFilter()) {
                $search_spec['filter'] = $list_state->getFilter();
            }
            $document_service->walkDocuments($search_spec, 50, function($document) use ($csv_export)
            {
                $csv_export->publish($document);
            });

            return $csv_export->getStorage()->getResource();
        };

        if ($stream_id === CsvStorage::OUTPUT_STREAM) {
            return $generate_csv;
        }

        $csv_resource = $generate_csv();
        rewind($csv_resource);

        return $csv_resource;
    }

    public function executeZip(\AgaviRequestDataHolder $parameters)
    {
        $response = $this->getResponse();
        $archiveName = $this->getAttribute('module')->getName() . '-List.zip';
        $response->setHttpHeader('Content-disposition', 'attachment; filename=' . $archiveName);

        $zipArchive = $this->getAttribute('zip_file');

        return fopen($zipArchive->getArchivePath(), 'r+');
    }

    protected function setBreadcrumb()
    {
        $listState = $this->getAttribute('state');
        $module = $this->getAttribute('module');

        $listRouteName = sprintf('%s.list', $module->getOption('prefix'));
        $page = round($listState->getOffset() / $listState->getLimit()) + 1;
        $routing = $this->getContext()->getRouting();

        $tm = $this->getContext()->getTranslationManager();
        $moduleName = $tm->_($module->getName(), 'modules.labels');
        $moduleCrumb = array(
            'text' => $moduleName,
            'link' => $routing->gen($listRouteName),
            'info' => $moduleName . ' - ' . $tm->_('List view (start)', 'modules.labels'),
            'icon' => 'hb-icon-list'
        );

        $breadcrumbs = $this->getContext()->getUser()->getAttribute('breadcrumbs', 'honeybee.breadcrumbs', array());
        if (1 <= count($breadcrumbs))
        {
            array_splice($breadcrumbs, 1);
        }

        $routeParams = array(
            'offset' =>  $listState->getOffset(),
            'limit' => $listState->getLimit(),
            'sorting' => array(
                'field' => $listState->getSortField(),
                'direction' => $listState->getSortDirection()
            )
        );

        if (! $listState->hasSearch() && ! $listState->hasFilter())
        {
            $breadcrumbs = array(array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $moduleName . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'hb-icon-list'
            ));
        }
        else if ($listState->hasSearch())
        {
            $routeParams['search'] = $listState->getSearch();
            $breadcrumbs[] = array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $tm->_('Search for:', 'modules.labels') . ' ' . $listState->getSearch() . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'hb-icon-search'
            );
        }
        else if ($listState->hasFilter())
        {
            $routeParams['filter'] = $listState->getFilter();
            $breadcrumbs[] = array(
                'text' => $moduleName,
                'link' => $routing->gen($listRouteName, $routeParams),
                'info' => $tm->_('Extended Search', 'modules.labels') . ' (' . $tm->_('Page', 'modules.labels') . ' ' . $page . ')',
                'icon' => 'hb-icon-search'
            );
        }

        $list_setting_name = sprintf('%s_last_list_url', $module->getOption('prefix'));
        $this->getContext()->getUser()->setAttribute($list_setting_name, $routing->gen(null), 'honeybee.list');

        $this->getContext()->getUser()->setAttribute('modulecrumb', $moduleCrumb, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }

    protected function getSidebarTreeRelationData()
    {
        $module = $this->getAttribute('module');
        $treeRelationData = array();
        $referenceFields = $module->getFields(array(), array('Dat0r\Core\Field\ReferenceField'));

        foreach ($referenceFields as $referenceField)
        {
            foreach ($referenceField->getReferencedModules() as $referencedModule)
            {
                if ($referencedModule->isActingAsTree())
                {
                    $treeRelationData[] = array(
                        'treeModule' => get_class($referencedModule),
                        'localModule' => get_class($module),
                        'referenceField' => $referenceField->getName()
                    );
                }
            }
        }

        return $treeRelationData;
    }
}
