<?php

// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));
$libsDir = $rootDir . DIRECTORY_SEPARATOR . 'libs';
$ezComponentsDir = $libsDir . DIRECTORY_SEPARATOR . 'ezc';
$phpUnitDir = $libsDir . DIRECTORY_SEPARATOR . 'PHPUnit';

$includes = array($libsDir, $ezComponentsDir, $phpUnitDir);
set_include_path(implode(PATH_SEPARATOR, $includes).PATH_SEPARATOR.get_include_path());

$testingEnabled = false;

// make generated files group writeable for easy switch between web/console
umask(02);

require 'agavi/agavi.php';
require $rootDir . DIRECTORY_SEPARATOR . '/app/config.php';

// +---------------------------------------------------------------------------+
// | Setup ezcomponents autoloading.                                           |
// +---------------------------------------------------------------------------+
require $ezComponentsDir . DIRECTORY_SEPARATOR . 'Base/src/ezc_bootstrap.php';
spl_autoload_register(array('ezcBase', 'autoload'));

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $rootDir . DIRECTORY_SEPARATOR . 'app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load($testingEnabled);


// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();

Agavi::bootstrap(
    ProjectEnvironmentConfig::toEnvString()
);

?>