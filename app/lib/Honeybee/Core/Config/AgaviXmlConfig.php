<?php

namespace Honeybee\Core\Config;

/**
 * The AgaviXmlConfig's job is to provide a strategy for loading and handling xml config-resources.
 * At the moment we let agavi's array config handler do the work of parsing the xml for us.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class AgaviXmlConfig extends ArrayConfig
{
    /**
     * Load the given $config_source and returns an array representation.
     *
     * @param mixed $config_source path to configuration file to load
     *
     * @return array of settings loaded from given source
     *
     * @throws Honeybee\Core\Config\ConfigException when loading fails
     */
    protected function load($config_source)
    {
        return parent::load(
            include \AgaviConfigCache::checkConfig($config_source, \AgaviConfig::get('core.default_context'))
        );
    }
}
