<?php

namespace Honeybee\Core\Storage\CouchDb;

use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Agavi\Database\CouchDb\Database;
use CouchdbClientException;

class Storage implements IStorage
{
    /**
     * The name of couchdb's internal id field.
     */
    const COUCH_ID = '_id';

    /**
     * The name of couchdb's internal revision field.
     */
    const COUCH_REV = '_rev';

    /**
     * The name of the document's id field.
     */
    const DOC_IDENTIFIER = 'identifier';

    /**
     * The name of the document's revision field.
     */
    const DOC_REVISION = 'revision';

    /**
     * The name of the field we store the document's type meta information in.
     * The type data is used by the factory method to determine the correct document implementor
     * and is added/removed transparently before data is stored/hydrated.
     * Carefull with the choice of name as you may overwrite document data,
     * if the document has a member with the same name.
     */
    const DOC_IMPLEMENTOR = 'type';

    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

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
        catch(CouchdbClientException $e)
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
     * @param Document $document The document to save.
     *
     * @throws Exception If writing to couchdb fails for some reason.
     */
    public function writeOne(Document $document)
    {
        $couchDb = $this->getDatabase()->getConnection();
        $data = $this->mapDomainDataToCouchDb($document);
        $result = $couchDb->storeDoc(NULL, $data);

        if(isset($result['error']) && 'conflict' === $result['error'])
        {
            $data[self::COUCH_REV] = $couchDb->statDoc(NULL, $document->getIdentifier());
            $result = $couchDb->storeDoc(NULL, $data);
        }

        if (isset($result['ok']) && isset($result['rev']))
        {
            $document->setIdentifier($result['id']);
            $document->setRevision($result['rev']);
        }
        else
        {
            throw new Exception(
                "Failed to store document: " . $document->getIdentifier()
            );
        }
    }

    public function writeMany(array $documents)
    {
        return $this->storeBulkData(
            $this->prepareBulkData($documents)
        );
    }

    public function delete(Document $document)
    {
        $couchDb = $this->getDatabase()->getConnection();

        var_dump("Deleting " . $document->getIdentifier() . ' - rev. ' . $document->getRevision());

        $couchDb->deleteDoc(NULL, $document->getIdentifier(), $document->getRevision());
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

            $curData = $this->mapDomainDataToCouchDb($document);
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
                $document->setIdentifier($docId);
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
                    $document->setIdentifier($docId);
                }
            }
        }

        return $errors;
    }

    protected function nextUuid(Document $document)
    {
        $uuids = $this->getDatabase()->getConnection()->nextUuids();

        return sprintf('%s-%s', $document->getModule()->getOption('prefix'), $uuids[0]);
    }

    /**
     * Turn the given document into an array representation, 
     * that can directly be passed to couchdb as is.
     * Basically this means mapping the document's id and rev fields,
     * to couch's id and rev fields and making sure that the self::DOC_IDENTIFIER
     * value is set correctly to reflect the current type.
     *
     * @param Document $document
     *
     * @return array
     */
    protected function mapDomainDataToCouchDb(Document $document)
    {
        $data = $document->toArray();

        $data[self::DOC_IMPLEMENTOR] = get_class($document);

        if (isset($data[self::DOC_IDENTIFIER]) && ! empty($data[self::DOC_IDENTIFIER]))
        {
            $data[self::COUCH_ID] = $data[self::DOC_IDENTIFIER];
            unset($data[self::DOC_IDENTIFIER]);
        }
        else
        {
            $data[self::COUCH_ID] = $this->nextUuid($document);
        }

        if (isset($data[self::DOC_REVISION]) && ! empty($data[self::DOC_REVISION]))
        {
            $data[self::COUCH_REV] = $data[self::DOC_REVISION];
            unset($data[self::DOC_REVISION]);
        }

        return $data;
    }

    /**
     * Turn the given (couchdb result)array into an array representation
     * that can directly be passed to an Document's create method as is.
     * Basically this means mapping the couch's id and rev fields,
     * to the document's id and rev fields and making sure that the self::DOC_IDENTIFIER field is removed.
     *
     * @param array $data
     *
     * @return array
     */
    protected function mapCouchDbDataToDomain(array $data)
    {
        $docType = isset($data[self::DOC_IMPLEMENTOR]) ? $data[self::DOC_IMPLEMENTOR] : FALSE;

        if (! $docType || ! class_exists($docType, TRUE))
        {
            throw new Exception(
                "Invalid or corrupt type information within document data."
            );
        }

        unset($data[self::DOC_IMPLEMENTOR]);

        if (isset($data[self::COUCH_ID]))
        {
            $data[self::DOC_IDENTIFIER] = $data[self::COUCH_ID];
            unset($data[self::COUCH_ID]);
        }

        if (isset($data[self::COUCH_REV]))
        {
            $data[self::DOC_REVISION] = $data[self::COUCH_REV];
            unset($data[self::COUCH_REV]);
        }

        return $data;
    }

    protected function getDatabase()
    {
        return $this->database;
    }
}
