<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Service\IService;
use Honeybee\Core\Storage\CouchDb;
use Honeybee\Core\Config;

class Service implements IService
{
    private $module;

    private $exports;

    private $exportDefinitions;

    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->exports = array();

        $this->exportDefinitions = new Config\AgaviXmlConfig(
            \AgaviConfig::get('core.modules_dir') . '/' . $module->getName() . '/config/exports.xml'
        );
    }

    public function export($exportName, Document $document)
    {
        return $this->getExport($exportName)->export($document);
    }

    public function getExports()
    {
        $exportNames = array_keys($this->exportDefinitions->get());
        $exports = array();

        foreach ($exportNames as $name)
        {
            $exports[] = $this->getExport($name);
        }

        return $exports;
    }

    public function getExport($name)
    {
        if (! isset($this->exports[$name]))
        {
            $this->exports[$name] = $this->createExport($name);
        }

        return $this->exports[$name];
    }

    protected function createExport($name)
    {
        if ($this->exportDefinitions->has($name))
        {
            $params = $this->exportDefinitions->get($name);
        }
        else
        {
            // @todo garcon! export specific exceptions s'il vouz plait.
            throw new \InvalidArgumentException("Trying to load not configured export.");
        }

        $implementor = $params['class'];
        $description = $params['description'];
        $settings = new Config\ArrayConfig($params['settings']);

        // @todo get rid of being specific about couchdb here and generalize storage creation.
        $database = \AgaviContext::getInstance()->getDatabaseManager()->getDatabase($settings->get('connection'));
        $storage = new CouchDb\GenericStorage($database);

        $export = new $implementor($settings, $storage, $name, $description);
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
