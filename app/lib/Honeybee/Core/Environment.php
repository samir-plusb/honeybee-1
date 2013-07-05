<?php

namespace Honeybee\Core;

/**
 * The Environment provides essential settings for bootstrapping this application,
 * such as the environment to use and depending on that load the correct application settings.
 * The settings provided by this class are always in a local (environment) dedicated and therefore
 * the bin/configure-env script must be run once to set things up before a fresh application can be run.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class Environment
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * The name of 'database' environment setting.
     */
    const CFG_DB = 'database';

    /**
     * The name of 'php' environment setting, a path to a php executable binary.
     */
    const CFG_PHP = 'php';

    /**
     * The name of 'hostname' environment setting.
     */
    const CFG_HOSTNAME = 'hostname';

    /**
     * The name of 'environment' config setting.
     */
    const CFG_ENVIRONMENT = 'environment';

    /**
     * The name of our database-host setting.
     */
    const CFG_DB_HOST = 'host';

    /**
     * The name of our database-port setting.
     */
    const CFG_DB_PORT = 'port';

    /**
     * The name of our base-href setting.
     */
    const CFG_BASE_HREF = 'base_href';

    /**
     * The name (wihtout prefix) of the local config file that holds our env-settings.
     */
    const CONFIG_FILE_NAME = 'environment.php';

    /**
     * A file prefix that is used to indicate local only files.
     * This can be used to let your scm ignore local.* files
     * and prevent accidently comitting sensitive data.
     */
    const CONFIG_FILE_PREFIX = 'local.';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds an instance of this class.
     *
     * @var         Environment
     */
    private static $instance;

    /**
     * Holds the data from our local config file.
     *
     * @var         array
     */
    private $config;

    /**
     * The hostname of the host we're running on.
     * May be faked for cli requests in order to still generate web urls.
     *
     * @var         string
     */
    private $current_host;

    /**
     * Boolean flag that indicates whether we are in testing mode or not.
     *
     * @var         boolean
     */
    private $testing_enabled;

    /**
     * Holds a string that is used as a suffix to a given environment.
     *
     * @var         string
     */
    private $environment_modifier;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new Environment instance.
     *
     * @param       boolean $testing_enabled If testing is enabled
     */
    private function __construct($testing_enabled = FALSE, $environment_modifier = '')
    {
        $this->environment_modifier = $environment_modifier;

        $baseDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        $local_config_dir =
            $baseDir . DIRECTORY_SEPARATOR .
            'etc' . DIRECTORY_SEPARATOR .
            'local' . DIRECTORY_SEPARATOR;

        $filename = $this->testing_enabled ? 'testing.' . self::CONFIG_FILE_NAME : self::CONFIG_FILE_NAME;
        $config_filepath = $local_config_dir . self::CONFIG_FILE_PREFIX . $filename;

        $this->config = include($config_filepath);

        if (isset($_SERVER['HTTP_HOST']))
        {
            $this->current_host = $_SERVER['HTTP_HOST'];
        }
        // No override allowed for testing environments.
        if (($env = getenv('AGAVI_ENVIRONMENT')) && TRUE !== $testing_enabled)
        {
            $this->config[self::CFG_ENVIRONMENT] = $env;
        }

        if (! empty($environment_modifier))
        {
            $this->config[self::CFG_ENVIRONMENT] .= $environment_modifier;
        }
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Initialize our config instance, thereby loading our evironment settings.
     *
     * @param       boolean $testing_enabled
     *
     * @return      Environment
     */
    public static function load($testing_enabled = FALSE, $environment_modifier = '')
    {
        if (NULL === self::$instance)
        {
            self::$instance = new Environment($testing_enabled, $environment_modifier);
        }

        return self::$instance;
    }

    /**
     * Return the current environment's string representation.
     *
     * @return      string
     */
    public static function toEnvString()
    {
        return self::getEnvironment();
    }

    /**
     * Return the current environment.
     *
     * @return      string
     */
    public static function getEnvironment()
    {
        return self::$instance->config[self::CFG_ENVIRONMENT];
    }

    /**
     * Return the current environment.
     *
     * @return      string
     */
    public static function getEnvironmentModifier()
    {
        return self::$instance->environment_modifier;
    }

    /**
     * Return the current environment.
     *
     * @return      string
     */
    public static function getCleanEnvironment()
    {
        $environment = self::getEnvironment();
        $modifier = self::getEnvironmentModifier();

        return str_replace($modifier, '', $environment);
    }

    /**
     * Return the path to the cli php binary to use for the current environment.
     *
     * @return      string
     */
    public static function getPhpPath()
    {
        return self::$instance->config[self::CFG_PHP];
    }

    /**
     * Return an array containing our current env specific database settings.
     *
     * @return      array
     */
    public static function getDatabaseSettings()
    {
        return self::$instance->config[self::CFG_DB];
    }

    /**
     * Return our current env's database port setting.
     *
     * @return      int
     */
    public static function getDatabasePort()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_PORT];
    }

    /**
     * Return our current env's database host setting.
     *
     * @return      string
     */
    public static function getDatabaseHost()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_HOST];
    }

    /**
     * Return our current env's base url.
     *
     * @return      string
     */
    public static function getBaseHref()
    {
        return self::$instance->config[self::CFG_BASE_HREF];
    }

    /**
     * Tells you if we are currently in testing mode.
     * Not interesting most cases as the testing environment switches transparently
     * for your project code.
     *
     * @return      boolean
     */
    public static function isTestingEnabled()
    {
        return self::$instance->testing_enabled;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}
