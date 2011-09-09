<?php

/**
 * The ImportConfigFileValidator class provides validation of import related config files
 * and checks for their existance and for schema violations.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Validation
 */
class ImportConfigFileValidator extends AgaviStringValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the file ending to consider when looking for config files.
     */
    const CONFIG_FILE_POSTFIX = '.xml';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <AgaviStringValidator OVERRIDES> -----------------------
    
    /**
     * Validates that their is a valid import config file for a provided config name.
     * 
     * @return      boolean
     * 
     * @see         AgaviStringValidator::validate()
     */
    protected function validate()
    {
        if (!parent::validate())
        {
            return FALSE;
        }
        
        $config = NULL;
        $originalValue =& $this->getData($this->getArgument());
        $filePath = $this->buildImportConfigFilePath($originalValue);
        
        try
        {
            $config = new DataImportFactoryConfig($filePath);
        }
        catch (Exception $e)
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
        
        $this->export($config, $this->getParameter('export', $this->getArgument()));
        
        return TRUE;
    }
    
    // ---------------------------------- </AgaviStringValidator OVERRIDES> ----------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Build an absolute file system path for a given $configName.
     * 
     * @param       string $configName
     * 
     * @return      string 
     */
    private function buildImportConfigFilePath($configName)
    {
        return AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR . 
            'modules' . DIRECTORY_SEPARATOR . 
            'Import' . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            'imports' . DIRECTORY_SEPARATOR . 
            $configName . self::CONFIG_FILE_POSTFIX;

    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>