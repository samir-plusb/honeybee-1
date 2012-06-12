<?php

/**
 * The BaseConfig class is an abstract implementation of the IConfig interface.
 * It fully exposes the required interface methods and defines the strategy for loading
 * a given config source.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Config
 */
abstract class BaseConfig implements IConfig
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An assoc array that holds our settings.
     *
     * @var         array
     */
    private $settings;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Load the given $configSource and return an array representation.
     *
     * @return      array
     */
    abstract protected function load($configSource);

    /**
     * Return an array of settings that are to be considered as mandatory for this instance.
     * An exception will occur upon initialization,
     * if a required setting is not available after loading.
     *
     * @return      array
     */
    abstract protected function getRequiredSettings();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <IConfig IMPL> -----------------------------------

    /**
     * Create a new BaseConfig instance for the given $configSource.
     *
     * @param       mixed $configSource
     *
     * @see         IConfig::__construct()
     *
     * @uses        BaseConfig::init()
     */
    public function __construct($configSource)
    {
        $this->init($configSource);
    }

    /**
     * Fetch the value for the given setting.
     * If the setting does not exist the provided default is returned.
     *
     * @param       string $setting
     * @param       mixed $default
     *
     * @return      mixed
     *
     * @see         IConfig::getSetting()
     */
    public function getSetting($setting, $default = NULL)
    {
        $value = $default;

        if (isset($this->settings[$setting]))
        {
            $value = $this->settings[$setting];
        }

        return $value;
    }

   /**
    * Return an array containing our supported setting names.
    *
    * @return       array
    *
    * @see          IConfig::getSupportSettings()
    */
    public function getSupportSettings()
    {
        return array_keys($this->settings);
    }

    // ---------------------------------- </IConfig IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Initialize this BaseConfig instance with the given $configSource.
     * After this method has completed we are ready to provide settings.
     *
     * @param       mixed $configSource
     *
     * @uses        BaseConfig::load()
     * @uses        BaseConfig::validateConfig()
     */
    protected function init($configSource)
    {
        $settings = $this->load($configSource);

        $this->validateConfig($settings);

        $this->settings = $settings;
    }

    /**
     * Validate the given settings against any required rules.
     * This basic implementation just makes sure,
     * that all required settings are in place.
     *
     * @param       array $settings
     *
     * @throws      ConfigException
     *
     * @uses        BaseConfig::getRequiredSettings()
     */
    protected function validateConfig(array $settings)
    {
        foreach ($this->getRequiredSettings() as $required_setting)
        {
            if (!isset($settings[$required_setting]))
            {
                throw new ConfigException(
                    "Missing setting '" . $required_setting . "' for config."
                );
            }
        }
    }

    // ---------------------------------- </WORKING METHODS> --------------------------------------
}

?>