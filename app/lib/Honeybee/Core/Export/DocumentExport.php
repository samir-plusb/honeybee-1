<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Config;

class DocumentExport implements IExport
{
    const PUBLISHED_AT_FIELD = 'publishedAt';

    private $name;

    private $description;

    private $settings;

    protected $storage;

    public function __construct(Config\ArrayConfig $settings, IStorage $storage, $name, $description)
    {
        $this->settings = $settings;
        $this->storage = $storage;
        $this->name = $name;
        $this->description = $description;
    }

    public function publish(Document $document)
    {
        $metaData = $document->getMeta();

        if (! isset($metaData[self::PUBLISHED_AT_FIELD]))
        {
            $publishDate = new \DateTime();
            $metaData[self::PUBLISHED_AT_FIELD] = $publishDate->format(DATE_ISO8601);
            $document->setMeta($metaData);
        }

        $data = $this->buildExportData($document);
        $data['identifier'] = $document->getShortIdentifier();
        $data['type'] = $document->getModule()->getOption('prefix');
        $data[self::PUBLISHED_AT_FIELD] = $metaData[self::PUBLISHED_AT_FIELD];

        $this->storage->write($data);
    }

    public function revoke(Document $document)
    {
        $identifier = $document->getShortIdentifier();

        if ($data = $this->storage->read($identifier))
        {
            $this->storage->delete($identifier, $data['revision']);
            
            foreach ($this->filters as $filter)
            {
                $filter->onDocumentRevoked($document);
            }
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

    protected function buildExportData(Document $document)
    {
        $data = array();

        foreach ($this->filters as $filter)
        {
            $data = array_merge($data, $filter->execute($document));
        }

        return $data;
    }

    protected function getStorage()
    {
        return $this->storage;
    }
}
