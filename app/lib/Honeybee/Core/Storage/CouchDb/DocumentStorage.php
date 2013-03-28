<?php

namespace Honeybee\Core\Storage\CouchDb;

use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Agavi\Database\CouchDb\Database;
use Honeybee\Agavi\Database\CouchDb\ClientException;

class DocumentStorage extends BaseStorage
{
    /**
     * Read a document's data from couchdb.
     *
     * @param type $identifier The document identifier to look for.
     * @param type $revision The document revision to return.
     *
     * @return array The document's data (domain structure).
     *
     * @throws CouchdbClientException When something goes wrong while reading from couchdb.
     */
    public function read($identifier, $revision = NULL)
    {
        $identifier = trim($identifier); // just in case
        $data = NULL;

        if (empty($identifier))
        {
            return $data;
        }

        try
        {
            $couchDb = $this->getDatabase()->getConnection();
            $data = $this->mapCouchDbDataToDomain(
                $couchDb->getDoc(NULL, $identifier, $revision)
            );
        }
        catch(ClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                // no document for the given id in our current database.
                $data = NULL;
            }
            else
            {
                throw $e;
            }
        }

        return $data;
    }

    /**
     * Write the given document to couchdb.
     *
     * @param mixed $document The document to save.
     *
     * @throws Exception If writing to couchdb fails for some reason.
     */
    public function writeOne($document)
    {
        $couchDb = $this->getDatabase()->getConnection();

        $data = $this->mapDomainDataToCouchDb(
            get_class($document), 
            $document->toArray()
        );

        $result = $couchDb->storeDoc(NULL, $data);

        if(isset($result['error']) && 'conflict' === $result['error'])
        {
            $data[self::COUCH_REV] = $couchDb->statDoc(NULL, $document->getIdentifier());
            $result = $couchDb->storeDoc(NULL, $data);
        }

        if (isset($result['ok']) && isset($result['rev']))
        {
            $document->setRevision($result['rev']);
        }
        else
        {
            throw new \Exception(
                "Failed to store document: " . $document->getIdentifier()
            );
        }
    }

    public function writeMany(array $bulkData)
    {
        return $this->storeBulkData(
            $this->prepareBulkData($bulkData)
        );
    }

    public function delete($identifier, $revision = NULL)
    {
        $couchDb = $this->getDatabase()->getConnection();
        $couchDb->deleteDoc(NULL, $identifier, $revision);
    }

    protected function prepareBulkData(array $documents)
    {
        $data = array();

        foreach ($documents as $document)
        {
            if (! $document instanceof Document)
            {
                throw new Exception(
                    "Invalid object type passed to bulkSave invocation." . PHP_EOL .
                    "Only Document instances allowed."
                );
            }

            $curData = $this->mapDomainDataToCouchDb(
                get_class($document), 
                $document->toArray()
            );

            $data[$curData[self::COUCH_ID]] = array('doc' => $document, 'data' => $curData);
        }

        return $data;
    }

    protected function storeBulkData(array $data)
    {
        $couchDb = $this->getDatabase()->getConnection();
        $conflictedData = array();
        $errors = array();

        $couchDocs = array_map(function($item) { return $item['data']; }, $data);
        foreach ($couchDb->storeDocs(NULL, array_values($couchDocs)) as $docResult)
        {
            $docId = $docResult['id'];
            if (isset($docResult['error']) && 'conflict' === $docResult['error'])
            {
                $document->setRevision($couchdb->statDoc(NULL, $docId));
                $conflictedData[] = $data[$docId];
            }
            else if (isset($docResult['error']))
            {
                $errors[$docId] = $docResult['error'];
            }
            else
            {
                $document = $data[$docId]['doc'];
                $document->setRevision($docResult['rev']);
            }
        }

        if (! empty($conflictedData))
        {
            foreach ($couchdb->storeDocs(NULL, $conflictedData) as $docResult)
            {
                $docId = $docResult['id'];
                if(isset($docResult['error']))
                {
                    $errors[$docId] = $docResult['error'];
                }
                else
                {
                    $document = $data[$docId]['doc'];
                    $document->setRevision($docResult['rev']);
                }
            }
        }

        return $errors;
    }
}
