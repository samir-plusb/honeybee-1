<?php

$testingEnabled = true;

require dirname(__DIR__) . '/app/dispatch.php';

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