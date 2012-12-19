<?php

class HoneybeeModuleService
{
    public function getModules()
    {
        $modules = array();
        $iter = new DirectoryIterator(AgaviConfig::get('core.modules_dir'));
        foreach($iter as $file) 
        {
            $configPath = $file->getPathname() .
                str_replace('/', DIRECTORY_SEPARATOR, '/config/dat0r/module.xml');

            if (is_readable($configPath))
            {
                $moduleName = str_replace(
                    AgaviConfig::get('core.modules_dir') . DIRECTORY_SEPARATOR, 
                    '', 
                    $file->getPathname()
                );
                // @todo refactor hardcoded namespace to be appropiately deynamic.
                $implementor = sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $moduleName);
                $factory = array($implementor, 'getInstance');

                if (is_callable($factory))
                {
                    $modules[] = $implementor::getInstance();
                }
                else
                {
                    throw new Exception(
                        "Unable to call the '$implementor' module's getInstance method."
                    );
                }
            }
        }

        return $modules;
    }    
}
