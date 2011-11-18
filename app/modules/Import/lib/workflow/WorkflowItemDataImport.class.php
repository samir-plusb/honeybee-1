<?php

class WorkflowItemDataImport extends BaseDataImport
{
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
     * This method is responseable for converting data records
     * to a final array structure, suitable for data distribution to couchdb.
     *
     * @return      array
     */
    protected function convertRecord(IDataRecord $record)
    {
        $data = $record->toArray();
        $data[self::COUDB_ID_FIELD] = $record->getIdentifier();
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
    protected function processRecord()
    {
        $this->getCurrentRecord();
        return TRUE;
    }

    // ---------------------------------- </BaseDataImport IMPL> ---------------------------------
}

?>
