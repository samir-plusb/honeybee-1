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
 * @subpackage      Base
 */
abstract class BaseDataImport implements IDataImport
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the event that reflects successful records imports.
     *
     * Example:     Register to this event the following way:
     *
     *              ProjectEventProxy::getInstance()->subscribe(
     *                  BaseDataImport::EVENT_RECORD_SUCCESS,
     *                  $yourCallback
     *              );
     */
    const EVENT_RECORD_SUCCESS = 'midas.events.import.record_success';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds a reference to our config object.
     *
     * @var         IImportConfig $config
     */
    protected $config;

    /**
     * Holds a reference to the event proxy, that we use to publish our event to other components.
     *
     * @var         IEventProxy
     */
    protected $eventProxy;

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
    
    /**
     * Holds our import report.
     * 
     * @var         DataImportReport
     */
    protected $report;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * This method is called once for each record, that is delivered by our datasource,
     * when executing our run method.
     * Shall return true if the import succeeded and false otherwise.
     * Use the self::getCurrentRecord() method to obtain the current record.
     *
     * @return      boolean
     */
    protected abstract function processRecord();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <IDataImport IMPL> -------------------------------------

    /**
     * Create a new IDataImport instance.
     *
     * @param IImportConfig $config
     *
     * @see         IDataImport::__construct()
     */
    public function __construct(IImportConfig $config)
    {
        $this->config = $config;

        $this->eventProxy = ProjectEventProxy::getInstance();
    }

    /**
     * Run the import, hence shove the data to it's desitiny.
     *
     * @param       IDataSource $dataSource
     *
     * @see         IDataImport::run()
     *
     * @uses        BaseDataImport::init()
     * @uses        IDataSource::nextRecord()
     * @uses        BaseDataImport::processRecord()
     * @uses        BaseDataImport::cleanup()
     */
    public function run(IDataSource $dataSource)
    {
        $this->report = new DataImportReport();
        
        $this->init($dataSource);

        $doImport = TRUE;

        while ($doImport)
        {
            try
            {
                $doImport = (FALSE !== ($this->currentRecord = $dataSource->nextRecord()));

                if ($this->currentRecord instanceof IDataRecord)
                {
                    $this->processRecord();
                }
            }
            catch(Exception $e)
            {
                $doImport = FALSE;
                
                // @todo Handle error (log the stuff or something like that.
                throw $e;
            }
        }
        
        $this->cleanup();
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
     *  Returns the record, that is currently adressed for import.
     *
     * @return      IDataRecord
     *
     * @throws      DataImportException When called outside of the IDataImport's execution scope.
     */
    protected function getCurrentRecord()
    {
        if (NULL === $this->currentRecord)
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
        if (NULL === $this->dataSource)
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
        $this->dataSource = NULL;
        $this->currentRecord = NULL;
    }
    
    protected function onRecordSuccess(IDataRecord $record, $message)
    {
        $this->fireRecordImportedEvent($record);
        
        $this->report->addRecordSuccess($record, $message);
    }    

    protected function onRecordError(IDataRecord $record, $message)
    {
        $this->report->addRecordError($record, $message);
    }
    
    /**
     * Fire an event that indicates, that we have successfully imported the given record.
     *
     * @param       IDataRecord $dataRecord
     */
    protected function fireRecordImportedEvent(IDataRecord $dataRecord)
    {
        $this->eventProxy->publish(
            new ProjectEvent(self::EVENT_RECORD_SUCCESS, array('record' => $dataRecord))
        );
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>