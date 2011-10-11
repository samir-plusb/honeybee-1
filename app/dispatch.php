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

// make generated files group writeable for easy switch between web/console
umask(02);

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

?>