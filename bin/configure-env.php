<?php

$root_dir = dirname(__DIR__);
require_once $root_dir . '/etc/configure/EnvironmentConfigurator.class.php';
require_once $root_dir . '/etc/configure/ConfigureEnvScript.class.php';
require_once $root_dir . '/vendor/autoload.php';

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
