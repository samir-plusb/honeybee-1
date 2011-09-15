<?php

/**
 * The CouchDbDataImport class is an concrete implementation of the BaseDataImport base class.
 * It's task is to stuff records that are obtained from a given IDataSource into the configured couchdb.
 * In order to be able to tweak the couchdb load, this class supports buffering calls to importData
 * and sends batch-creates to the couch everytime the buffer limit is reached.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class CouchDbDataImport extends BaseDataImport
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * The CouchDb standard doc-id fieldname.
     */
    const COUDB_ID_FIELD = '_id';

    /**
     * The CouchDb standard doc-revision fieldname.
     */
    const COUDB_REV_FIELD = '_rev';

    /**
     * Name of the error returned by couchdb, when conflicts occur.
     */
    const COUCHDB_ERR_CONFLICT = 'conflict';

    /**
     * The default value to use for our @see CouchDbDataImport::importBufferSize.
     */
    const DEFAULT_BUFFER_SIZE = 50;

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the client, that we use in order to talk to our couch.
     *
     * @var         ExtendedCouchDbClient $couchClient
     */
    protected $couchClient;

    /**
     * Holds the import (record)data currently being buffered, before it is batch-pushed to couch.
     * The buffer is an assoc array whereas a data's record-identifier serves as the key.
     *
     * @var         array $importBuffer
     */
    protected $importBuffer;

    /**
     * Holds the size of the import-buffer for this instance.
     *
     * @var         int
     */
    protected $importBufferSize;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <BaseDataImport OVERRIDES> -----------------------------

    /**
     * Creates a new CouchDbDataImport instance.
     *
     * @param       CouchDbDataImportConfig $config
     *
     * @throws      DataImportException If the given $config is no CouchDbDataImportConfig.
     *
     * @see         BaseDataImport::__construct()
     */
    public function __construct(IImportConfig $config)
    {
        if (!$config instanceof CouchDbDataImportConfig)
        {
            throw new DataImportException(
                "Invalid config object given. Instance of CouchDbDataImportConfig expected, got: " . get_class($config)
            );
        }

        parent::__construct($config);
    }

    /**
     * Let our parent do it's work and then get our
     * couchDbClient and importBuffer setup for import execution.
     *
     * @param       IDataSource $dataSource
     *
     * @see         BaseDataImport::init()
     *
     * @uses        CouchDbDataImport::buildCouchDbUri()
     */
    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);

        $this->couchClient = new ExtendedCouchDbClient(
            $this->buildCouchDbUri()
        );

        $this->importBuffer = array();
        $this->importBufferSize = $this->config->getSetting(
            CouchDbDataImportConfig::PARAM_BUFFER_SIZE,
            self::DEFAULT_BUFFER_SIZE
        );
    }

    /**
     * Cleanup our state after running the import.
     * This method flushes the importBuffer in order to make sure there are no leftovers.
     *
     * @see         BaseDataImport::cleanup()
     *
     * @uses        CouchDbDataImport::flushImportBuffer()
     */
    protected function cleanup()
    {
        $this->flushImportBuffer();

        parent::cleanup();
    }

    /**
     * Converts the given record into an array,
     * thereby setting the couchdb id field.
     *
     * @return      array
     *
     * @see         BaseDataImport::convertRecord()
     */
    protected function convertRecord()
    {
        $data = parent::convertRecord();

        $data[self::COUDB_ID_FIELD] = $this->getCurrentRecord()->getIdentifier();

        return $data;
    }

    // ---------------------------------- </BaseDataImport OVERRIDES> ----------------------------


    // ---------------------------------- <BaseDataImport IMPL> ----------------------------------

    /**
     * Implementation of the BaseDataImport's importData strategy hook.
     * In this case we add the data to our buffer and then flush if necessary.
     *
     * @param       array $data
     *
     * @return      boolean
     *
     * @uses        CouchDbDataImport::flushImportBuffer()
     */
    protected function importData(array $data)
    {
        $this->importBuffer[$data[self::COUDB_ID_FIELD]] = $data;

        if ($this->importBufferSize === count($this->importBuffer))
        {
            $this->flushImportBuffer();
        }

        return TRUE;
    }

    // ---------------------------------- </BaseDataImport IMPL> ---------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Sends our buffered import data to the couch.
     *
     * @uses        CouchDbDataImport::resolveConflicts()
     */
    protected function flushImportBuffer()
    {
        $database = $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_DATABASE);
        $couchData = array_values($this->importBuffer);

        $updateData = $this->handleCouchDbResponse(
            $this->couchClient->storeDocs($database, $couchData)
        );

        if (!empty($updateData))
        {
            // @todo Handle unresolveable conflicts (exception?).
            $this->couchClient->storeDocs($database, $updateData);
        }
    }

    /**
     * Checks the given reponse to our batch create request,
     * checks for conflicts and returns an array that can used to resolve them.
     *
     * @param       array $response
     *
     * @return      array
     */
    protected function handleCouchDbResponse(array $response)
    {
        $updateData = array();

        foreach ($response as $resultItem)
        {
            if ($this->isCouchDbCreateConflict($resultItem))
            {
                if (NULL != ($newData = $this->createUpdateData($resultItem['id'])))
                {
                    $updateData[] = $newData;
                }
            }
        }

        return $updateData;
    }

    /**
     * Helper method for querying a couchdb result for a potential create conflict.
     *
     * @param       array $response
     *
     * @return      boolean
     */
    protected function isCouchDbCreateConflict(array $response)
    {
        return (
            isset($response['error'])
            &&
            self::COUCHDB_ERR_CONFLICT === $response['error']
        );
    }

    /**
     * Return an array that can be posted to couch as is
     * and that contains a valid revision
     * in order to update it's corresponding document.
     *
     * @param       string $docId
     *
     * @return      array or NULL if no update necessary
     */
    protected function createUpdateData($docId)
    {
        $database = $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_DATABASE);
        $rev = $this->couchClient->statDoc($database, $docId);

        if (0 !== $rev)
        {
            $data = $this->importBuffer[$docId];
            $oldDoc = $this->couchClient->getDoc($database, $docId);
            foreach ($data as $key => $val)
            {
                if (! array_key_exists($key, $oldDoc) || 0 != strcmp(serialize($val), serialize($oldDoc[$key])))
                {
                    $data[self::COUDB_REV_FIELD] = $rev;
                    return $data;
                }
            }
        }

        return NULL;
    }

    /**
     * Build the uri to use in order to connect to couchdb.
     *
     * @return string
     */
    protected function buildCouchDbUri()
    {
        return sprintf(
            "http://%s:%d/",
            $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_HOST),
            $this->config->getSetting(CouchDbDataImportConfig::CFG_COUCHDB_PORT)
        );
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>