<?php

/**
 * EnvironmentConfigurator provides an simple api to update/initialize your environment and host configuration.
 * It is meant for command line usage and required user interaction in 3 of 4 public methods (@see self::importHosts).
 *
 * @package    BerlinOnline
 * @subpackage Configure
 *
 * @author     Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @copyright  BerlinOnline GmbH & Co. KG
 *
 * @version $Id: EnvironmentConfigurator.class.php 4182 2011-06-08 12:22:07Z tschmitt $
 */
class EnvironmentConfigurator
{
    // ---------------------------------- <CONSTANTS> ----------------------------------------------

    const CFG_DB_HOST = 'database.host';

    const CFG_DB_PORT = 'database.port';

    /**
     * Holds the char that we consider as a positve response from a user on the cli.
     */
    const CONFIRM_POSITIVE = 'y';

    /**
     * Holds the char that we consider as a positve response from a user on the cli.
     */
    const CONFIRM_NEGATIVE = 'n';

    // --------------------------------- </CONSTANTS> ----------------------------------------------


    // --------------------------------- <PUBLIC METHODS> --------------------------------------------

    /**
     * Starts an interactive user dialog prompting for basic environment information,
     * such as env-name, and database settings.
     *
     * @todo Add asset-basepath and further basic settings.
     */
    public function initializeMainConfig()
    {
        print("- Section: Common Settings" . PHP_EOL);
        $php_path = $this->promptPhpPath();

        while (!($environment = $this->promptEnvironment()));

        print(PHP_EOL . "- Section: Database Settings" . PHP_EOL);
        $database_settings = $this->promptDatabaseSettings();

        $config = array(
            ProjectEnvironmentConfig::CFG_PHP         => $php_path,
            ProjectEnvironmentConfig::CFG_ENVIRONMENT => $environment,
            ProjectEnvironmentConfig::CFG_DB          => $database_settings
        );

        $config_filepath = $this->getConfigFilePath();
        $config_settings = array();

        if (is_readable($config_filepath))
        {
            $config_settings = include $config_filepath;
        }

        $this->generateConfig($config);
        $this->generateLocalConfigSh($config);
    }

    protected function promptPhpPath()
    {
        $php_command = null;
        $default_php_path = isset($_SERVER['PHP_COMMAND']) ? $_SERVER['PHP_COMMAND'] : @exec('which php');

        while (!$this->testPhp($php_command))
        {
            $php_command = $this->readline('Enter path to php', $default_php_path);
        }

        return $php_command;
    }

    protected function promptEnvironment()
    {
        $environment = null;

        while (!$this->testEnvironment($environment))
        {
            $environment = $this->readline('Enter environment');
        }

        $answer = $this->readline(
            "Environment is '" . $environment . PHP_EOL . "Are you sure you want to keep this?(y/n)"
        );

        return (self::CONFIRM_POSITIVE === $answer) ? $environment : false;
    }

    protected function promptDatabaseSettings()
    {
        $db_settings = array();

        while (!$this->testDatabaseSettings($db_settings))
        {
            $db_settings[ProjectEnvironmentConfig::CFG_DB_HOST]   = $this->readline('Enter host', 'localhost');

            while (!is_numeric($port = $this->readline('Enter port', '5984')));
            $db_settings[ProjectEnvironmentConfig::CFG_DB_PORT] = (int) $port;
        }

        return $db_settings;
    }

    protected function readline($label, $default = null, $promptchar = ':', $hide_input = false)
    {
        print(
            empty($default)
            ? sprintf("%s%s ", $label, $promptchar)
            : sprintf("%s[%s]%s ", $label, $default, $promptchar)
        );

        if ($hide_input)
        {
            system('stty -echo');
        }

        $value = trim(fgets(STDIN));

        if ($hide_input)
        {
            system('stty echo');
            print(PHP_EOL);
        }

        if (0 === strlen($value) && null !== $default)
        {
            return $default;
        }

        return $value;
    }

    // --------------------------------- <USER PROMPTING> ---------------------------------------------


    // -------------------------------- <CONFIG GENERATION> -------------------------------------------

    protected function generateConfig(array $config)
    {
        $config_filepath = $this->getConfigFilePath();

        $config_code = sprintf(
            $this->getConfigCodeTemplateString(),
            var_export($config, true)
        );

        if (false === file_put_contents($config_filepath, $config_code))
        {
            // @todo Throw an exception or warn about the error.
        }
    }

    protected function generateLocalConfigSh(array $config)
    {
        $sh_config_filepath = $this->getLocalConfigShFilePath();

        if (!file_exists($sh_config_filepath))
        {
            $config_code = sprintf(
                $this->getLocalShConfigCode(),
                $config[ProjectEnvironmentConfig::CFG_PHP]
            );

            if (false === file_put_contents($sh_config_filepath, $config_code))
            {
                // @todo Throw an exception or warn about the error.
            }
        }
    }

    protected function getConfigCodeTemplateString()
    {
        return <<<PHP_CODE
<?php

/**
 * !CAUTION! Autogenerated code that was generated by the 'configure-env.php' script.
 * All modifications directly applied to this file,
 * may break or badly influence the operability of the apps relying on the contents of this file.
 *
 * !DO NOT EDIT!
 * Unless there is someone standing behind you with a loaded shotgun ready to create a mess and still you have to know what or doing or
 * !DO NOT EDIT!
 */

return %s;
PHP_CODE;
    }

    protected function getLocalShConfigCode()
    {
        return <<<SH_CODE
#!/bin/bash
export PHP_COMMAND=%s
SH_CODE;
    }

    // -------------------------------- </CONFIG GENERATION> -------------------------------------------


    // ---------------------------------- <VALUE CHECKING> ---------------------------------------------

    protected function testPhp($path)
    {
        if (empty($path)) return false;

        $output = array();
        exec("$path -v", $output);

        if (1 < count($output) && strstr($output[0], 'PHP 5.3'))
        {
            return true;
        }

        return false;
    }

    protected function testEnvironment($environment)
    {
        return !empty($environment) && 3 <= strlen($environment);
    }

    protected function testDatabaseSettings(array $database_settings)
    {
        return true;
        if (empty ($database_settings)) return false;

        $port = $database_settings[ProjectEnvironmentConfig::CFG_DB_PORT];
        $host = $database_settings[ProjectEnvironmentConfig::CFG_DB_HOST];

        try
        {
            $client = new CouchDbClient(sprintf('http://%s:%d/', $host, $port));
        }
        catch (PDOException $e)
        {
            print($e->getMessage() . PHP_EOL);

            return false;
        }

        return true;
    }

    // ---------------------------------- </VALUE CHECKING> ---------------------------------------------


    // ----------------------------------- <PATH HANDLING> ----------------------------------------------

    protected function getAppbasePath()
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    protected function getLocalSettingsBasePath()
    {
        $base_dir = $this->getAppbasePath();

        return $base_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR;
    }

    protected function getConfigFilePath()
    {
        $local_dir = $this->getLocalSettingsBasePath();

        return $local_dir . ProjectEnvironmentConfig::CONFIG_FILE_NAME;
    }

    protected function getLocalConfigShFilePath()
    {
        $local_dir = $this->getLocalSettingsBasePath();

        return $local_dir . 'local.config.sh';
    }

    // ----------------------------------- </PATH HANDLING> ---------------------------------------------
}
