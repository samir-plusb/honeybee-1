<?php

/**
 * The SimpleConfig class is an abstract implementation of the ImportBaseConfig base.
 * It's job is to provide a strategy for handling simple, data-based configuration.
 * In short, this guy wraps assoc arrays into config objects.
 * Just extend this class, provide your required settings via {@see ImportBaseConfig::getRequiredSettings()}
 * and pass in your settings-data to the constructor.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/Config
 */
abstract class SimpleConfig extends ImportBaseConfig
{
    // ---------------------------------- <ImportBaseConfig METHODS> -----------------------------

    /**
     * Load the given $configSource and return an array representation.
     *
     * @return      array<string, mixed>
     *
     * @throws      ImportConfigException
     */
    protected function load($configSrc)
    {
        if (!is_array($configSrc))
        {
            throw new ImportConfigException("The given config source is expected to be by the type of 'array' but is not.");
        }

        return $configSrc;
    }

    // ---------------------------------- </ImportBaseConfig METHODS> ----------------------------
}

?>