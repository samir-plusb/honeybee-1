<?php
// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$root_dir = dirname(dirname(__FILE__));
$base_dir = dirname(__FILE__);
$libs_dir = $root_dir . DIRECTORY_SEPARATOR . 'libs';

$includes = array($libs_dir, $libs_dir . DIRECTORY_SEPARATOR . 'PHPUnit');
set_include_path(implode(PATH_SEPARATOR, $includes).PATH_SEPARATOR.get_include_path());

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
//require $root_dir . DIRECTORY_SEPARATOR . 'app/lib/config/ProjectEnvironmentConfig.class.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/testing.php script.              |
// +---------------------------------------------------------------------------+
require 'agavi/testing.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our config.php script.                     |
// +---------------------------------------------------------------------------+
require $base_dir . DIRECTORY_SEPARATOR . 'config.php';

$arguments = AgaviTesting::processCommandlineOptions();

if(isset($arguments['environment'])) {
	$env = $arguments['environment'];
	unset($arguments['environment']);
} else {
	$env = 'testing';
}


AgaviToolkit::clearCache();
AgaviTesting::bootstrap($env);

PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.agavi_dir'));
PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));

AgaviTesting::dispatch($arguments);

?>