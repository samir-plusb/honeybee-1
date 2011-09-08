<?php

/**
 * The ImportBaseDataSource class is an abstract implementation of the IDataSource interface.
 * It provides an base implementation of most IDataRecord methods and provides template methods
 * for inheriting classes to hook into normalizing the incoming data on record creation.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataSource
 */
abstract class ImportBaseDataSource implements IDataSource
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds a reference our config object.
     * 
     * @var         IImportConfig 
     */
    protected $config;
    
    /**
     *
     * @var         A flag indicating whether we have been initialized or not. 
     */
    private $isInitialized = FALSE;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------
    
    /**
     * This method is called once upon the first call to our nextRecord method.
     * This is the place to set things up for retrieving your data.
     */
    abstract protected function init();
    
    /**
     * This method is responseable for moving on to the next set of data
     * coming from the data source that we reflect.
     * 
     * @return      boolean Returns true if there is still data available and false otherwise.
     */
    abstract protected function forwardCursor();
    
    /**
     * This method is responseable for actually retrieving the raw data,
     * that we are pointing to after forwardCursor() invocations, from our source.
     * 
     * @return      array
     */
    abstract protected function fetchData();
    
    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    
    
    // ---------------------------------- <IDataSource IMPL> -------------------------------------
    
    /**
     * Create a new ImportBaseDataSource instance.
     * 
     * @param       IImportConfig $config 
     * 
     * @see         IDataSource::__construct()
     */
    public function __construct(IImportConfig $config)
    {
        $this->config = $config;
    }
    
    /**
     * Return the next IDataRecord from our data source.
     * 
     * @return      IDataRecord
     * 
     * @see         IDataSource::nextRecord()
     * 
     * @uses        ImportBaseDataSource::init()
     * @uses        ImportBaseDataSource::forwardCursor()
     * @uses        ImportBaseDataSource::createRecord()
     * @uses        ImportBaseDataSource::fetchData()
     */
    public function nextRecord()
    {
        if (!$this->isInitialized)
        {
            $this->init();
            $this->isInitialized = TRUE;
        }
        
        if (!$this->forwardCursor())
        {
            return NULL;
        }
        
        return $this->createRecord(
            $this->fetchData()
        );
    }
    
    // ---------------------------------- </IDataSource IMPL> ------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Create a new concrete IDataRecord from the given raw data
     * coming from our fetchData() method.
     * 
     * @param       mixed $rawData
     * 
     * @return      IDataRecord 
     * 
     * @throws      DataSourceException If the configured record class is invalid.
     */
    protected function createRecord($rawData)
    {
        $recordClass = $this->config->getSetting(DataSourceConfig::CFG_RECORD_TYPE);

        if (!class_exists($recordClass, TRUE))
        {
            throw new DataSourceException(
                sprintf(
                    "Unable to find provided datarecord class: %s",
                    $recordClass
                )
            );
        }

        $record = new $recordClass($rawData);

        if (!($record instanceof IDataRecord))
        {
            throw new DataSourceException(
                sprintf(
                    "An invalid IDataRecord implementor was provided. '%s' does not implement the interface IDataRecord.",
                    $recordClass
                )
            );
        }

        return $record;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>