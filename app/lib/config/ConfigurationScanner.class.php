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
                // scan for supported agavi config files
                $configPath = $file->getPathname().str_replace('/', DIRECTORY_SEPARATOR, '/config/');
                foreach (glob($configPath.'*.xml') as $configFile)
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
                // scan for module specific dat0r packages
                $dat0rPath = $file->getPathname().str_replace('/', DIRECTORY_SEPARATOR, '/lib/dat0r/');
                if (is_dir($dat0rPath))
                {
                    $directoryIter = new DirectoryIterator($dat0rPath);
                    foreach ($directoryIter as $package)
                    {
                        $packageName = $package->getFilename();
                        if ('.' === $packageName || '..' === $packageName)
                        {
                            continue;
                        }
                        if (! isset($configsToInclude['dat0r']))
                        {
                            $configsToInclude['dat0r'] = array();
                        }
                        $configsToInclude['dat0r'][] = $package->getPathName();
                    }
                }
            }
        }

        return $configsToInclude;
    }
}
