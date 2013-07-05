<?php

namespace Honeybee\Core\Config;

use Honeybee\Core\Config\ConfigException;

/**
 * The BaseConfig class is an abstract implementation of the IConfig interface.
 * It fully exposes the required interface methods and defines the strategy for loading
 * a given config source.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
abstract class BaseConfig implements IConfig
{
    /**
     * An assoc array that holds our settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * Load the given $configSource and return an array representation.
     *
     * @return array
     */
    abstract protected function load($config_source);

    /**
     * Create a new BaseConfig instance for the given $config_source.
     *
     * @param mixed $config_source
     */
    public function __construct($config_source)
    {
        $this->init($config_source);
    }

    /**
     * Fetch the value for the given setting.
     *
     * If the setting does not exist the provided default is returned.
     *
     * @param string $setting name of setting to get a value for
     * @param mixed $default default value to return if setting does not exist; defaults to null.
     *
     * @return mixed value of the setting or the given default value (may be null)
     */
    public function get($setting = null, $default = null)
    {
        if (!$setting)
        {
            return $this->settings;
        }
        
        $value = $default;

        if (isset($this->settings[$setting]))
        {
            $value = $this->settings[$setting];
        }

        return $value;
    }

    /**
     * Tells if we have a value for a given setting.
     *
     * @param string $setting name of setting to check
     *
     * @return boolean true if setting exists, false otherwise
     */
    public function has($setting)
    {
        return array_key_exists($setting, $this->settings);
    }

    /**
     * Initialize this BaseConfig instance with the given $config_source.
     * After this method has completed we are ready to provide settings.
     *
     * @param mixed $config_source
     */
    protected function init($config_source)
    {
        $settings = $this->load($config_source);

        $this->validateConfig($settings);

        $this->settings = $settings;
    }

    /**
     * Validate the given settings against any required rules.
     * This basic implementation just makes sure,
     * that all required settings are in place.
     *
     * @param array $settings
     *
     * @throws ConfigException
     */
    protected function validateConfig(array $settings)
    {
        foreach ($this->getRequiredSettings() as $required_setting)
        {
            if (!isset($settings[$required_setting]))
            {
                throw new ConfigException(
                    "Missing mandatory setting '" . $required_setting . "' for config."
                );
            }
        }
    }

    /**
     * Return an array of settings that are to be considered as mandatory for this instance.
     * An exception will occur upon initialization,
     * if a required setting is not available after loading.
     *
     * @return array
     */
    protected function getRequiredSettings()
    {
        return array();
    }
}
