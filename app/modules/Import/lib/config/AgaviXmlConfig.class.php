<?php

/**
 * The AgaviXmlConfig class is an abstract implementation of the ResourceBasedConfig base class.
 * It's job is to provide a strategy for loading and handling xml config-resources.
 * In order to do so, it implements the {@see ResourceBasedConfig::loadResource()} method.
 * At the moment we let agavi's array config handler do the work of parsing the xml for us.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Config
 */
abstract class AgaviXmlConfig extends ResourceBasedConfig
{
    // ---------------------------------- <ResourceBasedConfig METHODS> --------------------------

    /**
     * Load and return the settings data from our config source uri.
     *
     * @return      array
     *
     * @see         ResourceBasedConfig::loadResource()
     */
    protected function loadResource()
    {
        $uriParts = $this->getUriParts();
        $config = include AgaviConfigCache::checkConfig($uriParts['path']);

        if (empty($config))
        {
            return array();
        }

        return $config;
    }

    // ---------------------------------- </ResourceBasedConfig METHODS> -------------------------
}

?>