<?php

// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path to libs    |
// | which is where our vendor libraries reside (pear stuff etcetera).         |
// +---------------------------------------------------------------------------+
$root_dir = dirname(dirname(__FILE__));
$testing_dir = dirname(__FILE__);

$libs_dir = $root_dir . DIRECTORY_SEPARATOR . 'libs';
$phpunit_dir = $libs_dir . DIRECTORY_SEPARATOR . 'PHPUnit';

$includes = array($libs_dir, $phpunit_dir);
$include_path = implode(PATH_SEPARATOR, $includes);
set_include_path($include_path.PATH_SEPARATOR.get_include_path());

// +---------------------------------------------------------------------------+
// | Require and bootstrap our environment config-provider.                    |
// | Pass in true for the testing_enabled,                                     |
// | so ProjectEnvironmentConfig will load our test-settings.                  |
// +---------------------------------------------------------------------------+
require $root_dir . DIRECTORY_SEPARATOR . 'app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load(true);

// +---------------------------------------------------------------------------+
// | Then setup the rest and run our suites.                                   |
// +---------------------------------------------------------------------------+
require 'agavi/testing.php';
require $testing_dir . DIRECTORY_SEPARATOR . 'config.php';

AgaviToolkit::clearCache();

AgaviTesting::bootstrap(
    ProjectEnvironmentConfig::toEnvString()
);

PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.agavi_dir'));
PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));

AgaviTesting::dispatch(
    AgaviTesting::processCommandlineOptions()
);

?>