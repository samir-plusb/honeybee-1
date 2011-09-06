<?php

/**
 * The BaseDataImport class is an abstract implementation of the IDataImport interface, flyweight style.
 * It's task is to implement the IDataImport interface as for as possible for this level of abstraction,
 * thereby defining the basic strategy for handling data-imports.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataImport
 */
abstract class BaseDataImport implements IDataImport
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds a reference to our config object.
     * 
     * @var         IImportConfig $config
     */
    protected $config;
    
    /**
     * During execution time of our run method,
     * this member holds a reference to our currently used IDataSource.
     * 
     * @var         IDataSource $dataSource
     */
    private $dataSource;
    
    /**
     * During execution time of our run method,
     * this member holds a reference to our currently used IDataRecord.
     * 
     * @var         IDataRecord $currentRecord
     */
    private $currentRecord;

    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------
    
    /**
     * Import the given data.
     * 
     * @param       array<mixed> $data
     */
    protected abstract function importData(array $data);

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    
    
    // ---------------------------------- <IDataImport IMPL> -------------------------------------
    
    /**
     * Create a new IDataImport instance.
     * 
     * @param IImportConfig $config 
     * 
     * @see         IDataImport::__construct($config)
     */
    public function __construct(IImportConfig $config)
    {
        $this->config = $config;
    }
    
    /**
     * Run the import, hence shove the data to it's desitiny.
     * 
     * @param       IDataSource $dataSource
     * 
     * @return      boolean
     * 
     * @uses        BaseDataImport::init($dataSource)
     * @uses        IDataSource::nextRecord()
     * @uses        BaseDataImport::processRecord()
     * @uses        BaseDataImport::cleanup()
     */
    public function run(IDataSource $dataSource)
    {
        $this->init($dataSource);

        while ($this->currentRecord = $dataSource->nextRecord())
        {
            if (!$this->processRecord())
            {
                // @todo Need to think of a smart error handling,
                // as the overall import process is not allowed to be affected by single record related errors.
            }
        }

        $this->cleanup();
        
        return true;
    }
    
    // ---------------------------------- <IDataImport IMPL> -------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * This method is called once at the beginning of the run method.
     * It's task is to set things up before importing the records provided by our datasource.
     * 
     * @param       IDataSource $dataSource 
     */
    protected function init(IDataSource $dataSource) 
    {
        $this->dataSource = $dataSource;
    }
    
    /**
     * This method is called once for each record, that is delivered by our datasource,
     * when executing our run method.
     * Shall return true if the import succeeded and false otherwise.
     * 
     * @return      boolean
     */
    protected function processRecord()
    {
        $data = $this->convertRecord();
        
        return $this->importData($data);
    }
    
    /**
     * This method is responseable for converting data records 
     * to a final array structure, suitable for data distribution.
     * A concrete BaseDataImport implementation must be either be able to
     * handle the default structure or override this method and define an own strategy,
     * because the returned array is passed to our abstract method BaseDataImport::importData.
     * 
     * @return      array<mixed>
     */
    protected function convertRecord()
    {
        return $this->getCurrentRecord()->toArray();
    }
    
    /**
     *  Returns the record, that is currently adressed for import.
     * 
     * @return      IDataRecord
     * 
     * @throws      DataImportException When called outside of the IDataImport's execution scope.
     */
    protected function getCurrentRecord()
    {
        if (null === $this->currentRecord)
        {
            throw new DataImportException(
                "The currentRecord member is only available inside the run method's execution scope."
            );
        }

        return $this->currentRecord;
    }
    
    /**
     * Returns the datasource being used for the current import.
     * 
     * @return      IDataSource
     * 
     * @throws      DataImportException When called outside of the IDataImport's execution scope.
     */
    protected function getDataSource()
    {
        if (null === $this->dataSource)
        {
            throw new DataImportException(
                "The dataSource member is only available inside the run method's execution scope."
            );
        }

        return $this->dataSource;
    }
    
    /**
     * Retain our original state after finishing an import.
     */
    protected function cleanup() 
    {
        $this->dataSource = null;
        $this->currentRecord = null;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>