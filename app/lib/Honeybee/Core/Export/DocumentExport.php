<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Agavi\Database\CouchDb\ClientException;

class DocumentExport implements IExport
{
    protected $name;

    protected $description;

    protected $module;

    protected $settings;

    protected $client;

    public function __construct($name, $description, Module $module, Config\ArrayConfig $settings)
    {
        $this->name = $name;
        $this->description = $description;
        $this->module = $module;
        $this->settings = $settings;
        $this->client = \AgaviContext::getInstance()->getDatabaseConnection(
            $this->settings->get('connection')
        );
    }

    public function export(Document $document)
    {
        $exportDoc = $this->loadExportDocument($document);

        $data = $this->buildExportData($document);

        $data['_id'] = $document->getShortIdentifier();
        $data['type'] = $document->getModule()->getOption('prefix');

        if ($exportDoc)
        {
            $data['_rev'] = $exportDoc['_rev'];
        }
        
        $this->client->storeDoc(NULL, $data);
    }

    public function setFilters(Filter\FilterList $filters)
    {
        $this->filters = $filters;
    }

    protected function loadExportDocument(Document $document)
    {
        $exportDocument = NULL;

        try
        {
            $exportDocument = $this->client->getDoc(NULL, $document->getShortIdentifier());
        }
        catch(ClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                // no document for the given id in our current database.
                $exportDocument = NULL;
            }
            else
            {
                throw $e;
            }
        }

        return $exportDocument;
    }

    protected function buildExportData(Document $document)
    {
        $data = array();

        foreach ($this->filters as $filter)
        {
            $data = array_merge(
                $data,
                $filter->execute($document)
            );
        }

        return $data;
    }
}
