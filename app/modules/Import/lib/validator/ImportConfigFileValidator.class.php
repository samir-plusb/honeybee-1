<?php

class ImportConfigFileValidator extends AgaviStringValidator
{
    const CONFIG_FILE_POSTFIX = '.xml';
    
    protected function validate()
    {
        if (!parent::validate())
        {
            return FALSE;
        }
        
        $originalValue =& $this->getData($this->getArgument());
        
        $filePath = $this->buildImportConfigFilePath($originalValue);
        
        try
        {
            AgaviConfigCache::checkConfig($filePath);
        }
        catch (AgaviConfigurationException $e)
        {
            if (!$this->getParameter('pop_parse_errors'))
            {
                $this->throwError('parse_error');
            
                return FALSE;
            }
            
            throw $e;
        }
        
        if (!is_readable($filePath))
        {
            $this->throwError('non_existant');
            
            return FALSE;
        }
        
        return TRUE;
    }
    
    private function buildImportConfigFilePath($configName)
    {
        return AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR . 
            'modules' . DIRECTORY_SEPARATOR . 
            'Import' . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            'imports' . DIRECTORY_SEPARATOR . 
            $configName . self::CONFIG_FILE_POSTFIX;

    }
}

?>
