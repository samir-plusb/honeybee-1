<?php

class ConfigurationScanner
{
    protected static $supportedConfigs = array(
        'autoload', 'translation', 'settings', 'databases', 'routing'
    );

    public function scan()
    {
        $configsToInclude = array();
        $iter = new DirectoryIterator(AgaviConfig::get('core.modules_dir'));
        foreach($iter as $file) 
        {
            if($file->isDot())
            {
                continue;
            }
            
            $check = new AgaviModuleFilesystemCheck();
            $check->setConfigDirectory('config');
            $check->setPath($file->getPathname());

            if($check->check()) 
            {
                $configPath = $file->getPathname() . str_replace(
                    '/', DIRECTORY_SEPARATOR, '/config/'
                );
                foreach (glob($configPath . '*.xml') as $configFile)
                {
                    $name = str_replace('.xml', '', basename($configFile));
                    if (in_array($name, self::$supportedConfigs))
                    {
                        if (! isset($configsToInclude[$name]))
                        {
                            $configsToInclude[$name] = array();
                        }
                        $configsToInclude[$name][] = $configFile;
                    }
                }
            }
        }

        return $configsToInclude;
    }
}
