<?php

namespace Honeybee\Core\Export\Config;

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
     * Load the given $configSource and return an array representation.
     *
     * @return      array
     *
     * @throws      ConfigException
     */
    protected function load($configSrc)
    {
        return parent::load(
            include \AgaviConfigCache::checkConfig($configSrc)
        );
    }
}
