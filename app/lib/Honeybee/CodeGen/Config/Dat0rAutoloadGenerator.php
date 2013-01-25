<?php

namespace Honeybee\CodeGen\Config;

use \AgaviConfig;

class Dat0rAutoloadGenerator implements IConfigGenerator
{
    public function generate($name, array $affectedPaths)
    {
        $packageMap = array();

        foreach ($affectedPaths as $packagePath)
        {
            $packageName = basename($packagePath);
            $namespace = 'Honeybee\\Domain\\' . $packageName;
            $packageMap[$namespace] = $packagePath;
        }

        if (! empty($packageMap))
        {
            $autoloadPath = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 
            'includes' . DIRECTORY_SEPARATOR . 'autoload.php';

            file_put_contents($autoloadPath, $this->renderTemplate($packageMap));
        }
    }

    protected function renderTemplate(array $packageMap)
    {
        return sprintf($this->getTemplate(), str_replace(
            "'".AgaviConfig::get('core.app_dir'), 
            "dirname(dirname(__DIR__)).'", 
            var_export($packageMap, TRUE)
        ));
    }

    protected function getTemplate()
    {
        return <<<TPL
<?php

Dat0r\Autoloader::register(%s);

TPL;
    }
}
