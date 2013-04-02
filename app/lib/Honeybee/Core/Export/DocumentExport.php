<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;

class DocumentExport implements IExport
{
    protected $name;

    protected $description;

    protected $module;

    protected $settings;

    public function __construct($name, $description, Module $module, Config\ArrayConfig $settings)
    {
        $this->name = $name;
        $this->description = $description;
        $this->module = $module;
        $this->settings = $settings;
    }

    public function export(Document $document)
    {
        $data = array();

        foreach ($this->filters as $filter)
        {
            $data = array_merge(
                $data,
                $filter->execute($document)
            );
        }

        $connection = \AgaviContext::getInstance()->getDatabaseConnection(
            $this->settings->get('connection')
        );

        $connection->storeDoc(NULL, $data);
    }

    public function setFilters(Filter\FilterList $filters)
    {
        $this->filters = $filters;
    }
}
