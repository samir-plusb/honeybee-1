<?php

//#----------------------------------------------------------------------------------------------------------#
//#------------------------------------ DIRECTORIES & INCLUDES ----------------------------------------------#
//#----------------------------------------------------------------------------------------------------------#

$testing_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$lib_conf_dir = 'app/lib/config/';

$configurator_class_file = $testing_dir . '/etc/configure/EnvironmentConfigurator.class.php';
$configure_script_class_file = $testing_dir . '/etc/configure/ConfigureEnvScript.class.php';

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
foreach (array('ldap', 'fileinfo') as $extension)
{
    if (! extension_loaded($extension))
    {
        die('Please install and enable PHP extension: '.$extension);
    }
}
echo "pass.\n\n";

$configure_script = new ConfigureEnvScript();
$configure_script->run($argv);
