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
 * @subpackage      Base
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

    /**
     * Holds our name.
     *
     * @var         string
     */
    private $name;

    /**
     * Holds our description.
     *
     * @var         string
     */
    private $description;

    /**
     * Holds the logger we use to propagte our log messages.
     *
     * @var array
     */
    private $loggers;

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
     * @return      mixed
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
    public function __construct(IImportConfig $config, $name, $description = NULL)
    {
        $this->description = $description;
        $this->config = $config;
        $this->name = $name;

        $loggerManager = AgaviContext::getInstance()->getLoggerManager();
        $this->loggers = array(
            'debug' => $loggerManager->getLogger('debug'),
            'info'  => $loggerManager->getLogger('app'),
            'error' => $loggerManager->getLogger('error')
        );
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
            return FALSE;
        }

        $record = $this->createRecord(
            $this->fetchData()
        );

        $validationResult = $record->validate();

        if (!$validationResult->hasError())
        {
            return $record;
        }

        return NULL;
    }

    /**
     * Return our name.
     *
     * @return      string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return our description.
     *
     * @return      string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @throws      DataSourceException If the configured record creation fails unexpectedly.
     */
    protected function createRecord($rawData)
    {
        try
        {
            $recordConfig = new DataRecordConfig(
                array(
                    DataRecordConfig::CFG_SOURCE => $this->getCurrentOrigin(),
                    DataRecordConfig::CFG_ORIGIN => $this->getName()
                )
            );
            $recordClass = $this->getRecordImplementor();
            $record = new $recordClass($rawData, $recordConfig);
        }
        catch(DataRecordException $e)
        {
            throw new DataSourceException(
                sprintf(
                    "An error occured while trying to create new '%s' instance from data:\n%s\nOriginal Error:\n%s",
                    $recordClass,
                    $rawData,
                    $e->getMessage()
                )
            );
        }

        return $this->verifyRecordImplementation($record);
    }

    /**
     * Returns the IDataRecord implementation to use for creating new record instances.
     *
     * @return      string
     */
    protected function getRecordImplementor()
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

        return $recordClass;
    }

    /**
     * Verify that the given object is an implementation of the IDataRecord interface.
     *
     * @param       object $record
     *
     * @return      IDataRecord
     */
    protected function verifyRecordImplementation($record)
    {
        if (!($record instanceof IDataRecord))
        {
            throw new DataSourceException(
                sprintf(
                    "An invalid IDataRecord implementor was provided. " .
                    "'%s' does not implement the interface IDataRecord.",
                    get_class($record)
                )
            );
        }

        return $record;
    }

    /**
     * Return the origin of our current data record.
     *
     * @return      string
     */
    protected function getCurrentOrigin()
    {
        return '';
    }

    protected function logDebug($message)
    {
        $this->loggers['debug']->log(
            new AgaviLoggerMessage("[".get_class($this)."] $message", AgaviLogger::DEBUG)
        );
    }

    protected function logInfo($message)
    {
        $this->loggers['info']->log(
            new AgaviLoggerMessage("[".get_class($this)."] $message", AgaviLogger::INFO)
        );
    }

    protected function logError($message)
    {
        $this->loggers['error']->log(
            new AgaviLoggerMessage("[".get_class($this)."] $message", AgaviLogger::ERROR)
        );
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>