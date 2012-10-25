<?php

// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));

// make generated files group writeable for easy switch between web/console
umask(02);

$vendorDir = $rootDir . '/vendor';
require $vendorDir . '/autoload.php';
require $rootDir . '/app/config.php';

if (isset($testingEnabled))
{
    require $rootDir . '/testing/config.php';
    require $vendorDir . '/agavi/agavi/src/testing.php';
}

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $rootDir . '/app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load(isset($testingEnabled) && $testingEnabled);

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();
Agavi::bootstrap($_SERVER['AGAVI_ENVIRONMENT']);
