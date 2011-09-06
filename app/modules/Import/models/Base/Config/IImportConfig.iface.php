<?php

/**
 * IImportConfig implementations provide access to configration data,
 * that comes from arbitary config sources.
 * 
 * @copyright   BerlinOnline Stadtportal GmbH & Co. KG
 * @author      Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package     Import/Base
 * @subpackage  Config
 */
interface IImportConfig
{
    /**
     * Create a new IImportConfig instance,
     * that loads it's settings from the given configSource.
     * 
     * @param mixed $configSource
     */
    public function __construct($configSource);
    
    /**
     * Fetch the value for the given setting.
     * If the setting does not exist the provided default is returned.
     * 
     * @param string $setting
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getSetting($setting, $default = null);
    
    /**
     * Return an array containing the settings that are currently
     * avaiable by this instance.
     * 
     * @return array<string>
     */
    public function getSupportSettings();
}

?>