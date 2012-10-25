<?php

// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$rootDir = dirname(__DIR__);

// make generated files group writeable for easy switch between web/console
umask(02);

$vendorDir = $rootDir . '/vendor';

require $vendorDir . '/autoload.php';
require $rootDir . '/app/config.php';
require $rootDir . '/testing/config.php';
require $vendorDir . '/agavi/agavi/src/testing.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $rootDir . '/app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load(TRUE);

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+
// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();
AgaviTesting::bootstrap(ProjectEnvironmentConfig::toEnvString());

AgaviToolkit::clearCache();

// Workaround to prevent session_start() warnings after previous output
session_start();

AgaviTesting::getCodeCoverageFilter()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));
AgaviTesting::getCodeCoverageFilter()->addDirectoryToBlacklist(AgaviConfig::get('core.agavi_dir'));

$output = array();
$setupFixturesCmd = dirname(__FILE__) . '/setup_fixtures.sh';
// exec($setupFixturesCmd, $output);
//error_log(print_r($output, TRUE));

AgaviTesting::dispatch(AgaviTesting::processCommandlineOptions());
