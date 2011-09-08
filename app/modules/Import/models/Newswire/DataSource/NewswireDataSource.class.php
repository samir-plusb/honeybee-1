<?php
/**
 * Datasource for newswire imports
 *
 * @package Import
 * @subpackage Newswire
 * @author tay
 *
 */
class NewswireDataSource extends ImportBaseDataSource
{
    /**
     * @var GlobIterator Iterator over the newswire messages
     */
    protected $iterator;
    /**
     *
     * @var string path name to timestamp file
     */
    protected $timestampFile;
    /**
     *
     * @var int UNIX timestamp
     */
    protected $lastImportTime;

    public function __construct(IImportConfig $config)
    {
        parent::__construct($config);
        $this->timestampFile = $this->config->getSetting(NewswireDataSourceConfig::CFG_TIMESTAMP_FILE);
        ProjectEventProxy::getInstance()->subscribe(BaseDataImport::EVENT_RECORD_SUCCESS, array($this, 'importSucceeded'));
    }

    /**
     * initialize internal used GlobIterator
     *
     * @see ImportBaseDataSource::init()
     * @throws UnexpectedValueException if the path cannot be found.
     * @throws DataSourceException if timestamp file can not be written
     */
    protected function init()
    {
        if (! file_exists($this->timestampFile))
        {
            $this->resetTimestamp();
        }
        $this->lastImportTime = filemtime($this->timestampFile);
    }

    /**
     * move internal iterator to next file
     *
     * @return boolean TRUE if more data exists
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
     * @return
     */
    protected function fetchData()
    {
        $file = $this->iterator->current();
        $content = file_get_contents($file);
        $this->updateTimestamp($this->iterator->getMTime());
        return $content;
    }

    /**
     * store the timestamp of last imported record
     *
     * @param int $time unix timestamp
     * @throws DataSourceException if timestamp file can not be written
     */
    protected function updateTimestamp($time)
    {
        if (false === $time && $this->iterator instanceof DirectoryIterator)
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
     * @uses updateTimestamp()
     */
    public function importSucceeded()
    {
        $this->updateTimestamp($this->iterator->getMTime());
    }

    /**
     *
     * reset the timestamp
     * @uses updateTimestamp()
     */
    public function resetTimestamp()
    {
        $this->updateTimestamp(0);
    }

    /**
     * this is for the @'§x!"/(&/ phpunit process isolation
     * @see http://www.johnkleijn.nl/2010/Why-you-cant-or-shouldnt-unserialize-exceptions
     * @see https://github.com/sebastianbergmann/phpunit/issues/282
     */
    public function __sleep()
    {
        return array('config', 'timestampFile', 'lastImportTime');
    }
}