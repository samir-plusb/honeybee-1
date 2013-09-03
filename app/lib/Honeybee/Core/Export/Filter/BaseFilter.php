<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Config\IConfig;
use Honeybee\Core\Dat0r\BaseDocument;

/**
 * The BaseFilter class is an abstract implementation of the IFilter interface.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
abstract class BaseFilter implements IFilter
{
    /**
     * Holds our name.
     *
     * @var string
     */
    private $name;

    /**
     * Create a new filter instance from the the given config.
     *
     * @param IConfig $config
     */
    public function __construct($name, IConfig $config)
    {
        $this->config = $config;
        $this->name = $name;
    }

    /**
     * Return the filter's name.
     *
     * @return      string
     */
    public function getName()
    {
        return $this->name;
    }

    public function onDocumentRevoked(BaseDocument $document)
    {

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
