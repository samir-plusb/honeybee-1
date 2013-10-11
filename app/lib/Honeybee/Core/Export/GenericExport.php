<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Config;

class GenericExport implements IExport
{
    private $name;

    private $description;

    protected $settings;

    protected $storage;

    protected $filters;

    public function __construct(Config\ArrayConfig $settings, $name, $description)
    {
        $this->settings = $settings;
        $this->name = $name;
        $this->description = $description;
    }

    public function publish(Document $document)
    {
        $this->storage->write($this->buildExportData($document));
    }

    public function revoke(Document $document)
    {
        $identifier = $document->getShortIdentifier();
        $this->storage->delete($identifier, $document->getRevision());

        foreach ($this->filters as $filter)
        {
            $filter->onDocumentRevoked($document);
        }
    }

    public function setFilters(Filter\FilterList $filters)
    {
        $this->filters = $filters;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setStorage(IStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    protected function buildExportData(Document $document)
    {
        $data = array();

        foreach ($this->filters as $filter)
        {
            $data = array_merge($data, $filter->execute($document));
        }

        return $data;
    }
}
