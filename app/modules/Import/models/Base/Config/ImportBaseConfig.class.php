<?php

/**
 * The ImportBaseConfig class is an abstract implementation of the IImportConfig interface.
 * It fully exposes the required interface methods and defines the strategy for loading
 * a given config source.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/Config
 */
abstract class ImportBaseConfig implements IImportConfig
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An assoc array that holds our settings.
     *
     * @var         array<string, mixed>
     */
    private $settings;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Load the given $configSource and return an array representation.
     *
     * @return      array<string, mixed>
     */
    abstract protected function load($configSource);

    /**
     * Return an array of settings that are to be considered as mandatory for this instance.
     * An exception will occur upon initialization,
     * if a required setting is not available after loading.
     *
     * @return      array<string>
     */
    abstract protected function getRequiredSettings();

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <IImportConfig IMPL> -----------------------------------

    /**
     * Create a new ImportBaseConfig instance for the given $configSource.
     *
     * @param       mixed $configSource
     *
     * @see         IImportConfig::__construct()
     *
     * @uses        ImportBaseConfig::init()
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
     * @see         IImportConfig::getSetting()
     */
    public function getSetting($setting, $default = null)
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
    * @return       array<string>
    *
    * @see          IImportConfig::getSupportSettings()
    */
    public function getSupportSettings()
    {
        return array_keys($this->settings);
    }

    // ---------------------------------- </IImportConfig IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Initialize this ImportBaseConfig instance with the given $configSource.
     * After this method has completed we are ready to provide settings.
     *
     * @param       mixed $configSource
     *
     * @uses        ImportBaseConfig::load()
     * @uses        ImportBaseConfig::validateConfig()
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
     * @throws      ImportConfigException
     *
     * @uses        ImportBaseConfig::getRequiredSettings()
     */
    protected function validateConfig(array $settings)
    {
        foreach ($this->getRequiredSettings() as $required_setting)
        {
            if (!isset($settings[$required_setting]))
            {
                throw new ImportConfigException("Missing setting '" . $required_setting . "' for config.");
            }
        }
    }

    // ---------------------------------- </WORKING METHODS> --------------------------------------
}

?>