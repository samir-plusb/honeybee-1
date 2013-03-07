<?php

namespace Honeybee\Core\Import\Consumer;

use Honeybee\Core\Import\Config;
use Honeybee\Core\Import\Provider;
use Honeybee\Core\Import\Filter;
use Dat0r\Core\Runtime\Module;

/**
 * The ModuleConsumer class is a concrete implementation of the BaseConsumer base class.
 * It's task is to create Documents from the data it pulls from a given provider.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ModuleConsumer extends BaseConsumer
{
    /**
     * Holds a reference to the module we are creating documents for.
     * 
     * @var         Module
     */
    private $module;

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $moduleClass = $this->getConfig()->get('module');
        $this->module = $moduleClass::getInstance();
    }

    /**
     * Get the given data into the configured honeybee module.
     *
     * @param       array $data
     */
    protected function processData(array $data)
    {
        $this->module->getService()->save(
            $this->module->createDocument($data)
        );
    }
}
