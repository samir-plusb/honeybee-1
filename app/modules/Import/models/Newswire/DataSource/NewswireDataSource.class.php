<?php

/**
 * The NewswireDataSource class is a concrete implementation of the ImportBaseDataSource base class.
 * It provides fetching xml based data for our newswire providers from the filesystem.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Newswire
 */
class NewswireDataSource extends ImportBaseDataSource
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * @var         GlobIterator Iterator over the newswire messages
     */
    protected $iterator;
    /**
     *
     * @var         string path name to timestamp file
     */
    protected $timestampFile;
    /**
     *
     * @var         int UNIX timestamp
     */
    protected $lastImportTime;
    /**
     * @var         int UNIX timestamp of last fetched item
     */
    protected $lastItemModifiedTime;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------

    
    // ---------------------------------- <ImportBaseDataSource OVERRIDES> -----------------------
    
    /**
     * initialize timestamp file and subscribe to success events
     *
     * @param IImportConfig $config
     * @param string $name
     * @param string $description
     */
    public function __construct(IImportConfig $config, $name, $description = NULL)
    {
        parent::__construct($config, $name, $description = NULL);

        $this->timestampFile = $this->config->getSetting(
            NewswireDataSourceConfig::CFG_TIMESTAMP_FILE
        );

        ProjectEventProxy::getInstance()->subscribe(
            BaseDataImport::EVENT_RECORD_SUCCESS,
            array($this, 'importSucceeded')
        );
    }
    
    /**
     * Return a path to our current data origin.
     *
     * @return      string
     */
    protected function getCurrentOrigin()
    {
        return $this->iterator->current();
    }
    
    // ---------------------------------- </ImportBaseDataSource OVERRIDES> ----------------------
    
    
    // ---------------------------------- <ImportBaseDataSource IMPL> ----------------------------
    
    /**
     * initialize internal used GlobIterator
     *
     * @throws      UnexpectedValueException if the path cannot be found.
     * @throws      DataSourceException if timestamp file can not be written
     *
     * @see         ImportBaseDataSource::init()
     */
    protected function init()
    {
        if (! file_exists($this->timestampFile))
        {
            $this->resetTimestamp();
        }

        $this->lastImportTime = filemtime($this->timestampFile);
        $this->lastItemModifiedTime = $this->lastImportTime;
    }

    /**
     * move internal iterator to next file
     *
     * @return      boolean TRUE if more data exists
     */
    protected function forwardCursor()
    {
        if ($this->iterator)
        {
            $this->iterator->next();
        }
        else
        {
            $this->iterator = new GlobIterator($this->config->getSetting(NewswireDataSourceConfig::CFG_GLOB));
        }

        while ($this->iterator->valid())
        {
            if ($this->iterator->getMTime() > $this->lastImportTime)
            {
                return TRUE;
            }
            $this->iterator->next();
        }

        return FALSE;
    }

    /**
     * get data for createRecord()
     *
     * @return      array
     */
    protected function fetchData()
    {
        $file = $this->iterator->current();
        $content = file_get_contents($file);
        $this->lastItemModifiedTime = $this->iterator->getMTime();

        return $content;
    }

    // ---------------------------------- </ImportBaseDataSource IMPL> ---------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * store the timestamp of last imported record
     *
     * @param       int $time unix timestamp
     *
     * @throws      DataSourceException if timestamp file can not be written
     */
    protected function updateTimestamp($time)
    {
        if (FALSE === $time && $this->iterator instanceof DirectoryIterator)
        {
            $time = $this->iterator->getMTime();
        }

        if (! file_put_contents($this->timestampFile, $time))
        {
            throw new DataSourceException('Can not write timestamp file: '.$this->timestampFile);
        }

        touch($this->timestampFile, $time);
    }

    /**
     * run the method after a successfull import of current record
     *
     * @param       IEvent $event
     * @uses        updateTimestamp()
     */
    public function importSucceeded(IEvent $event)
    {
        $this->updateTimestamp($this->lastItemModifiedTime);
    }

    /**
     * reset the timestamp
     *
     * @uses        updateTimestamp()
     */
    public function resetTimestamp()
    {
        $this->updateTimestamp(0);
    }

    /**
     * this is for the @'§x!"/(&/ phpunit process isolation
     *
     * @see         http://www.johnkleijn.nl/2010/Why-you-cant-or-shouldnt-unserialize-exceptions
     * @see         https://github.com/sebastianbergmann/phpunit/issues/282
     */
    public function __sleep()
    {
        return array('config', 'timestampFile', 'lastImportTime');
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>