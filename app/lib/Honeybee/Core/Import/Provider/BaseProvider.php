<?php

namespace Honeybee\Core\Import\Provider;

use Honeybee\Core\Import\Config;

/**
 * The BaseProvider class is an abstract implementation of the IProvider interface.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
abstract class BaseProvider implements IProvider
{
    /**
     * Holds a reference our config object.
     *
     * @var IConfig
     */
    private $config;

    /**
     * Holds our name.
     *
     * @var string
     */
    private $name;

    /**
     * Holds our description.
     *
     * @var string
     */
    private $description;

    /**
     * A flag indicating whether we have been initialized or not.
     *
     * @var bool
     */
    private $isInitialized = FALSE;

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

    /**
     * This method is responseable for actually retrieving the raw data,
     * that we are pointing to after forwardCursor() invocations, from our source.
     *
     * @return      mixed
     */
    abstract protected function getCurrentOrigin();

    /**
     * Create a new provider instance from the the given config.
     * 
     * @param IConfig $config
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
     * Return the next item from our data source.
     *
     * @return      array
     */
    public function provideNextItem()
    {
        if (! $this->isInitialized)
        {
            $this->initialize();
            $this->isInitialized = TRUE;
        }

        if (! $this->forwardCursor())
        {
            return FALSE;
        }

        return $this->fetchData();
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
}
