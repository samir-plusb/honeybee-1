<?php

namespace Honeybee\Core\Export\Config;

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
     * @var         array
     */
    private $settings;

    /**
     * Load the given $configSource and return an array representation.
     *
     * @return      array
     */
    abstract protected function load($configSource);

    /**
     * Create a new BaseConfig instance for the given $configSource.
     *
     * @param       mixed $configSource
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
     */
    public function get($setting, $default = NULL)
    {
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
     * @param       string $setting
     *
     * @return      bool
     */
    public function has($setting)
    {
        return isset($this->settings[$setting]);
    }

    /**
     * Initialize this BaseConfig instance with the given $configSource.
     * After this method has completed we are ready to provide settings.
     *
     * @param       mixed $configSource
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

    /**
     * Return an array of settings that are to be considered as mandatory for this instance.
     * An exception will occur upon initialization,
     * if a required setting is not available after loading.
     *
     * @return      array
     */
    protected function getRequiredSettings()
    {
        return array();
    }
}
