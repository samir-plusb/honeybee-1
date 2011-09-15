<?php

//#----------------------------------------------------------------------------------------------------------#
//#------------------------------------ DIRECTORIES & INCLUDES ----------------------------------------------#
//#----------------------------------------------------------------------------------------------------------#

$testing_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$lib_conf_dir = 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

$environment_config_class_file = $testing_dir . $lib_conf_dir . 'ProjectEnvironmentConfig.class.php';
$configurator_class_file = $testing_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'configure' . DIRECTORY_SEPARATOR . 'EnvironmentConfigurator.class.php';
$configure_script_class_file = $testing_dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'configure' . DIRECTORY_SEPARATOR . 'ConfigureEnvScript.class.php';

require_once $environment_config_class_file;
require_once $configurator_class_file;
require_once $configure_script_class_file;

//#----------------------------------------------------------------------------------------------------------#
//#---------------------------------------------- MAIN ------------------------------------------------------#
//#----------------------------------------------------------------------------------------------------------#

echo "Check environment and extensions ...\n";
if (ini_get('safe_mode'))
{
    die('Please switch off "safe_mode"');
}
foreach (array('fileinfo', 'couchdb') as $extension)
{
    if (! extension_loaded('fileinfo'))
    {
        die('Please enable extension: '.$extension);
    }
}
echo "pass.\n\n";

$configure_script = new ConfigureEnvScript();
$configure_script->run($argv);
