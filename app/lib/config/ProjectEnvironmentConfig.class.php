<?php

class ProjectEnvironmentConfig
{
    const CFG_DB = 'database';
    
    const CFG_PHP = 'php';

    const CFG_HOSTNAME = 'hostname';

    const CFG_ENVIRONMENT = 'environment';

    const CFG_DB_HOST = 'host';

    const CFG_DB_PORT = 'port';

    const CONFIG_FILE_NAME = 'environment.php';
    
    const CONFIG_FILE_PREFIX = 'local.';
    
    private static $instance;

    private $config;

    private $current_host;
    
    private $testing_enabled;

    private function __construct($testing_enabled = false)
    {
        $this->testing_enabled = $testing_enabled;
        
        $base_dir = dirname(dirname(dirname(dirname(__FILE__))));
        $local_config_dir = $base_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR;
        $filename = $this->testing_enabled ? 'testing.' . self::CONFIG_FILE_NAME : self::CONFIG_FILE_NAME;
        $config_filepath = $local_config_dir . self::CONFIG_FILE_PREFIX . $filename;
        
        $this->config = include($config_filepath);
         
        if (isset($_SERVER['HTTP_HOST']))
        {
            $this->current_host = $_SERVER['HTTP_HOST'];
        }
    }
    
    public static function load($testing_enabled = false)
    {
        if (null === self::$instance)
        {
           self::$instance = new ProjectEnvironmentConfig($testing_enabled);
        }

        return self::$instance;
    }
    
    public static function toEnvString()
    {
        return self::getEnvironment();
    }

    public static function getEnvironment()
    {
        return self::$instance->config[self::CFG_ENVIRONMENT];
    }

    public static function getPhpPath()
    {
        return self::$instance->config[self::CFG_PHP];
    }

    public static function getDatabaseSettings()
    {
        return self::$instance->config[self::CFG_DB];
    }

    public static function getDatabasePort()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_PORT];
    }

    public static function getDatabaseHost()
    {
        $db_settings = self::getDatabaseSettings();

        return $db_settings[self::CFG_DB_HOST];
    }
    
    public static function isTestingEnabled()
    {
        return self::$instance->testing_enabled;
    }
}
