<?php

/**
 * IConfig implementations provide access to configration data,
 * that comes from arbitary config sources.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         Import
 * @subpackage      Config
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IConfig
{
    /**
     * Create a new IConfig instance,
     * that loads it's settings from the given configSource.
     *
     * @param       mixed $configSource
     */
    public function __construct($configSource);

    /**
     * Fetch the value for the given setting.
     * If the setting does not exist the provided default is returned.
     *
     * @param       string $setting
     * @param       mixed $default
     *
     * @return      mixed
     */
    public function getSetting($setting, $default = NULL);

    /**
     * Return an array containing the settings that are currently
     * avaiable by this instance.
     *
     * @return      array
     */
    public function getSupportSettings();
}

?>