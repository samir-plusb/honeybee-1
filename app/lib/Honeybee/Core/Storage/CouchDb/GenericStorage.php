<?php

namespace Honeybee\Core\Storage\CouchDb;

use Honeybee\Core\Storage\IStorage;
use Honeybee\Agavi\Database\CouchDb\Database;
use Honeybee\Agavi\Database\CouchDb\ClientException;

class GenericStorage implements IStorage
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
     * Write the given document to couchdb.
     *
     * @param mixed $document The document to save.
     *
     * @throws Exception If writing to couchdb fails for some reason.
     */
    public function write(array $data)
    {
        $couchDbClient = $this->getDatabase()->getConnection();
        $data = $this->mapDomainDataToCouchDb($data);

        $result = $couchDbClient->storeDoc(NULL, $data);
        $revision = NULL;

        if(isset($result['error']) && 'conflict' === $result['error'])
        {
            $data[self::COUCH_REV] = $couchDbClient->statDoc(NULL, $data[self::COUCH_ID]);
            $result = $couchDbClient->storeDoc(NULL, $data);
        }

        if (isset($result['ok']) && isset($result['rev']))
        {
            $revision = $result['rev'];
        }
        else
        {
            throw new \Exception("Failed to write data for doc-id: " . @$data[self::COUCH_ID]);
        }

        return $revision;
    }

    /**
     * Read a document's data from couchdb by identifier($key).
     * This method is not exposed to the repository and shall be used with caution.
     * For doing casual reads use a repositories find and read methods.
     *
     * @param string $key The document key to look up.
     * @param string $revision The document revision to select.
     *
     * @return array The document's data (domain structure).
     *
     * @throws CouchdbClientException When something goes wrong while reading from couchdb.
     */
    public function read($key, $revision = NULL)
    {
        $data = NULL;

        try
        {
            $data = $this->mapCouchDbDataToDomain(
                $this->getDatabase()->getConnection()->getDoc(NULL, $key, $revision)
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

    public function delete($key, $revision = NULL)
    {
        $couchDbClient = $this->getDatabase()->getConnection();
        $couchDbClient->deleteDoc(NULL, $key, $revision);
    }

    /**
     * Turn the given (domain entity) data into an array representation, 
     * that can directly be passed to couchdb as is.
     * Basically this means mapping the document's id and rev fields,
     * to couch's id and rev fields and making sure that the self::DOC_IDENTIFIER
     * value is set correctly to reflect the current type.
     *
     * @param array $data
     *
     * @return array
     */
    protected function mapDomainDataToCouchDb(array $data)
    {
        $docType = isset($data[self::DOC_IMPLEMENTOR]) ? $data[self::DOC_IMPLEMENTOR] : FALSE;
        if (! $docType)
        {
            throw new \Exception(
                "[mapDomainDataToCouchDb] Invalid or corrupt type information within document data. Type: " . $docType
            );
        }

        if (isset($data[self::DOC_IDENTIFIER]) && ! empty($data[self::DOC_IDENTIFIER]))
        {
            $data[self::COUCH_ID] = $data[self::DOC_IDENTIFIER];
            unset($data[self::DOC_IDENTIFIER]);
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

        if (! $docType)
        {
            throw new \Exception(
                "[mapCouchDbDataToDomain] Invalid or corrupt type information within document data. Type: " . $docType
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

    public function getDatabase()
    {
        return $this->database;
    }
}
