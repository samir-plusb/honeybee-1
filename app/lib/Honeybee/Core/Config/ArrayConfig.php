<?php

namespace Honeybee\Core\Config;

use Honeybee\Core\Config\ConfigException;

/**
 * The ArrayConfig class is an abstract implementation of the BaseConfig base.
 *
 * Its job is to provide a strategy for handling simple data configuration as
 * an associative array. Just extend this class, provide your required settings
 * via {@see BaseConfig::getRequiredSettings()} and pass in your settings data
 * to the constructor.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ArrayConfig extends BaseConfig
{
    /**
     * Load the given $config_source and return an array representation.
     *
     * @param array $config_source
     *
     * @return array
     *
     * @throws ConfigException if $config_source is not an array
     */
    protected function load($config_source)
    {
        if (!is_array($config_source))
        {
            throw new ConfigException("The given config source must be an array.");
        }

        return $config_source;
    }

    /**
     * @return array of all settings
     */
    public function toArray()
    {
        return $this->settings;
    }
}
