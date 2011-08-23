<?php

class ProjectEnvironmentConfig
{
    const CFG_DB = 'database';
    
    const CFG_PHP = 'php';

    const CFG_HOSTNAME = 'hostname';

    const CFG_ENVIRONMENT = 'environment';

    const CFG_DB_HOST = 'host';

    const CFG_DB_PORT = 'port';

    const CONFIG_FILE_NAME = 'local.environment.php';

    private static $instance;

    private $config;

    private $current_host;

    private function __construct($host = null)
    {
        $base_dir = dirname(dirname(dirname(dirname(__FILE__))));
        $config_filepath = $base_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        $this->config = include($config_filepath);
        
        if (isset($_SERVER['HTTP_HOST']))
        {
            $this->current_host = empty($host) ? $_SERVER['HTTP_HOST'] : $host;
        }
    }

    public static function getEnvironment()
    {
        $cfg = self::getInstance();

        return $cfg->config[self::CFG_ENVIRONMENT];
    }

    public static function getPhpPath()
    {
        $cfg = self::getInstance();

        return $cfg->config[self::CFG_PHP];
    }

    public static function getDatabaseSettings()
    {
        $cfg = self::getInstance();

        return $cfg->config[self::CFG_DB];
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

    public static function toEnvString()
    {
        return self::getEnvironment();
    }

    protected static function getInstance()
    {
        if (null === self::$instance)
        {
           self::$instance = new ProjectEnvironmentConfig();
        }

        return self::$instance;
    }
}
