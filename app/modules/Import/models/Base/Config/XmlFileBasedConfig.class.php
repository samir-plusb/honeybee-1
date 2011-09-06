<?php

/**
 * The XmlFileBasedConfig class is an abstract implementation of the ResourceBasedConfig base class.
 * It's job is to provide a strategy for loading and handling xml config-resources.
 * In order to do so, it implements the {@see ResourceBasedConfig::loadResource()} method.
 * At the moment we let agavi's array config handler do the work of parsing the xml for us.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class XmlFileBasedConfig extends ResourceBasedConfig
{
    // ---------------------------------- <ResourceBasedConfig METHODS> --------------------------

    /**
     * Load and return the settings data from our config source uri.
     *
     * @return      array<string, mixed>
     *
     * @see         ResourceBasedConfig::loadResource()
     */
    protected function loadResource()
    {
        $uriParts = $this->getUriParts();
        $config = include AgaviConfigCache::checkConfig($uriParts['path']);

        if (empty($config['import']))
        {
            return array();
        }

        return $config['import'];
    }

    // ---------------------------------- </ResourceBasedConfig METHODS> -------------------------
}

?>