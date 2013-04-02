<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Service\IService;

class Service implements IService
{
    private $module;

    private $filters;

    private $exports;

    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->exports = array();

        $this->exportsConfig = new Config\AgaviXmlConfig(
            \AgaviConfig::get('core.modules_dir') . '/' . $module->getName() . '/config/exports.xml'
        );
    }

    public function export($exportName, Document $document)
    {
        return $this->getExport($exportName)->export($document);
    }

    public function getExport($exportName)
    {
        if (! isset($this->exports[$exportName]))
        {
            $this->exports[$exportName] = $this->createExport($exportName);
        }

        return $this->exports[$exportName];
    }

    protected function createExport($exportName)
    {
        if ($this->exportsConfig->has($exportName))
        {
            $params = $this->exportsConfig->get($exportName);
        }

        $exportClass = $params['class'];

        $export = new $exportClass(
            $exportName, 
            $params['description'],
            $this->module,
            new Config\ArrayConfig($params['settings'])
        );
        $filters = new Filter\FilterList();

        foreach ($params['filters'] as $filterName => $filterParams)
        {
            $filters->add(
                new $filterParams['class'](
                    $filterName, 
                    new Config\ArrayConfig($filterParams['settings'])
                )
            );
        }

        $export->setFilters($filters);

        return $export;
    }
}
