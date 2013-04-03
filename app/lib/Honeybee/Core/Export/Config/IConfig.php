<?php

namespace Honeybee\Core\Export\Config;

/**
 * IConfig implementations provide access to configration data,
 * that comes from arbitary config sources.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IConfig
{
    /**
     * Fetch the value for the given setting.
     * If the setting does not exist the provided default is returned.
     *
     * @param       string $setting
     * @param       mixed $default
     *
     * @return      mixed
     */
    public function get($setting = NULL, $default = NULL);

    /**
     * Tells if we have a value for a given setting.
     *
     * @param       string $setting
     *
     * @return      bool
     */
    public function has($setting);
}
