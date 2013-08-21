<?php

namespace Honeybee\Core\Storage\ElasticSearch;

use Honeybee\Core\Storage\IStorage;
use Honeybee\Agavi\Database\ElasticSearch\Database;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\AbstractException;
use Elastica;

class GenericStorage implements IStorage
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Write the given document to elasticsearch.
     *
     * @param mixed $document The document to save.
     *
     * @throws Exception If writing to elasticsearch fails for some reason.
     */
    public function write(array $data)
    {
        $revision = null;
        $index = $this->database->getResource();

        $type = $index->getType($data['_type']);
        unset($data['_type']);

        $id = $data['_id'];
        unset($data['_id']);

        $document = null;

        try
        {
            $document = $type->getDocument($id);
        }
        catch(NotFoundException $e)
        {
            $document = new Elastica\Document($id);
        }

        $document->setData($data);
        $resp_data = $type->addDocument($document)->getData();
        $revision = $resp_data['_version'];

        return $revision;
    }

    /**
     * Read a document's data from elasticsearch by identifier($key).
     *
     * @param string $key The document key to look up.
     * @param string $revision The document revision to select.
     *
     * @return array The document's data (domain structure).
     *
     * @throws CouchdbClientException When something goes wrong while reading from elasticsearch.
     */
    public function read($key, $revision = NULL)
    {
        $document = null;

        try
        {
            if (is_array($key) && isset($key['_id']) && isset($key['_type']))
            {
                $index = $this->database->getResource();
                $type = $index->getType($key['_type']);
                $document = $type->getDocument($key['_id']);
            }
        }
        catch(AbstractException $e)
        {
            error_log($e->__toString());
        }

        return $document;
    }

    public function delete($key, $revision = NULL)
    {
        try
        {
            if (is_array($key) && isset($key['_id']) && isset($key['_type']))
            {
                $index = $this->database->getResource();
                $type = $index->getType($key['_type']);
                $resp_data = $type->deleteById($key['_id'])->getData();
            }
            else
            {
                error_log("Insufficient key data given to storage-delete: " . print_r($key, true));
                return false;
            }
        }
        catch(AbstractException $e)
        {
            error_log($e->__toString());
            return false;
        }

        return (bool)$resp_data['ok'];
    }

    public function getDatabase()
    {
        return $this->database;
    }
}
