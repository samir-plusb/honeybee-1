<?php

namespace Honeybee\Core\Import\Filter;

use Honeybee\Core\Config;

/**
 * The BaseFilter class is an abstract implementation of the IFilter interface.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class BaseFilter implements IFilter
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
     * A flag indicating whether we have been initialized or not.
     *
     * @var bool
     */
    private $isInitialized = FALSE;

    /**
     * Internal hook for subclasses to implement their execute strategy.
     *
     * @param       array $input
     *
     * @return      array
     */
    protected function run(array $input)
    {
        return $input;
    }

    /**
     * Create a new filter instance from the the given config.
     * 
     * @param IConfig $config
     */
    public function __construct(Config\IConfig $config, $name)
    {
        $this->config = $config;
        $this->name = $name;
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
     * Process the given input and return a corresponding deterministic output.
     *
     * @param       array $input
     *
     * @return      array
     */
    public function execute(array $input)
    {
        if (! $this->isInitialized)
        {
            $this->initialize();
            $this->isInitialized = TRUE;
        }

        return $this->run($input);
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
     * Return our config.
     *
     * @return      IConfig
     */
    protected function getConfig()
    {
        return $this->config;
    }
}
