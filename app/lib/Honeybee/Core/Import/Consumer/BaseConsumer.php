<?php

namespace Honeybee\Core\Import\Consumer;

use Honeybee\Core\Config;
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
abstract class BaseConsumer implements IConsumer
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
     * Holds a reference to a provider currently giving us data.
     *
     * @var         IProvider
     */
    private $currentProvider;

    /**
     * Proccess the given on the context of a concrete consumer context.
     * Usually this is the place where you would persist things,
     * hence get the actual import business done.
     *
     * @param       array $data
     */
    abstract protected function processData(array $data);

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
        $this->setUp($provider);

        while (($item = $provider->provideNextItem()))
        {
            try
            {
                $this->processData(
                    $this->executeFilters($item, $provider)
                );

                $this->report->addRecordSuccess($item);
            }
            catch(\Exception $e)
            {
                $this->report->addRecordError($item, $e->getMessage());
            }
        }

        $report = $this->report;

        $this->cleanUp();

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
     * Run our filters on the given data and return the outcome.
     *
     * @return      array
     */
    protected function executeFilters(array $item)
    {
        $processedData = $item;

        foreach ($this->filters as $filter)
        {
            $processedData = $filter->execute($processedData);
        }

        return $processedData;
    }

    /**
     * Return our current provider.
     *
     * @return      IProvider
     */
    protected function getCurrentProvider()
    {
        return $this->currentProvider;
    }

    /**
     * Called before kicking off consumption, sets up our runtime state.
     *
     * @param       IProvider $provider
     */
    protected function setUp(Provider\IProvider $provider)
    {
        $this->currentProvider = $provider;
        $this->report = new ConsumerReport();
    }

    /**
     * Called after consumption, resets our runtime state.
     */
    protected function cleanUp()
    {
        $this->currentProvider = NULL;
        $this->report = NULL;
    }
}
