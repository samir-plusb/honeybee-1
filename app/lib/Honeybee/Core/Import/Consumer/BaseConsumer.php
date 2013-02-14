<?php

namespace Honeybee\Core\Import\Consumer;

use Honeybee\Core\Import\Config;
use Honeybee\Core\Import\Provider;
use Honeybee\Core\Import\Filter;

/**
 * The BaseConsumer class is an abstract implementation of the IConsumer interface, flyweight style.
 * It's task is to implement the IConsumer interface as for as possible for this level of abstraction,
 * thereby defining the basic strategy for handling data-consumptions.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class BaseConsumer implements IConsumer
{
    /**
     * Holds our consumption report.
     *
     * @var         ConsumerReport
     */
    protected $report;

    /**
     * Holds a reference to our config object.
     *
     * @var         IConfig $config
     */
    private $config;

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
     * A flag indicating whether we have been initialized or not.
     *
     * @var         bool
     */
    private $isInitialized = FALSE;

    /**
     * Holds a list of filters that provider data is passed to.
     * 
     * @var         FilterList
     */
    private $filters;

    /**
     * Create a new BaseConsumer instance.
     *
     * @param       IConfig $config
     */
    public function __construct(Config\IConfig $config, $name, $description = '')
    {
        $this->config = $config;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array())
    {
        // empty impl. of the interface, override as needed.
        $this->isInitialized = TRUE;
    }

    /**
     * Set the filters to pass the data through during import.
     *
     * @param       FilterList $filters
     */
    public function setFilters(Filter\FilterList $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Run the consumption, hence shove the data to it's desitiny.
     *
     * @param       IProvider $provider
     */
    public function consume(Provider\IProvider $provider)
    {
        $this->report = new ConsumerReport();

        while (($item = $provider->provideNextItem()))
        {
            try
            {
                $this->processItem($item, $provider);
                $this->report->addRecordSuccess($item);
            }
            catch(\Exception $e)
            {
                $this->report->addRecordError($item, $e->getMessage());
            }
        }

        $report = $this->report;
        $this->report = NULL;

        return $report;
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

    /**
     * Return our config.
     *
     * @return      IConfig
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * This method is called once for each item, that is delivered by our provider,
     * when executing our run method.
     * Shall return true if the consumption succeeded and false otherwise.
     *
     * @return      boolean
     */
    protected function processItem(array $item, Provider\IProvider $provider)
    {
        $processedData = $item;

        foreach ($this->filters as $filter)
        {
            $processedData = $filter->execute($processedData);
        }

        var_dump(__METHOD__ . "  --  importing filtered data: " . print_r($processedData, TRUE));

        return TRUE;
    }
}
