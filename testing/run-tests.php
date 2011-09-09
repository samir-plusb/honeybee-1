<?php
// +---------------------------------------------------------------------------+
// | Require our dispatch script, that takes care loading libs and environment.|
// +---------------------------------------------------------------------------+
$testingEnabled = true;
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

// +---------------------------------------------------------------------------+
// | Then setup the rest and run our suites.                                   |
// +---------------------------------------------------------------------------+
require 'agavi/testing.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';

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