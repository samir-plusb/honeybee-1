<?php

/**
 * The AssetValidator class provides validation of asset resources given from various inputs
 * and always exports a valid asset uri that can be used with the ProjectAssetService.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Validation
 */
class AssetInfoValidator extends AgaviNumberValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our 'export_asset' parameter.
     * This parameter defines the parameter name inside our request-data
     * at which our validated IAssetInfo object will be exported to.
     */
    const PARAM_EXPORT = 'export';
    
    /**
     * Holds the default value that is used when no custom self::PARAM_EXPORT_ASSET has been defined.
     */
    const DEFAULT_EXPORT = 'asset_info';
    
    /**
     * Holds the name of the error thrown for invalid asset resources.
     */
    const ERR_INVALID_ASSET = 'invalid_asset_id';
    
    /**
     * Holds the name of the argument that specifies where to look for our asset resource.
     */
    const ARG_ASSET = 'aid';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <AgaviValidator OVERRIDES> -----------------------------
    
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
        
        $assetId = $this->getData($this->getArgument());
        
        try
        {
            $assetInfo = ProjectAssetService::getInstance()->get($assetId);
        }
        catch (CouchDbClientException $e)
        {
            $this->throwError();
            
            return FALSE;
        }
        
        if (NULL === $assetInfo)
        {
            $this->throwError('non_existant');
            
            return FALSE;
        }
        
        $this->export(
            $assetInfo,
            $this->getParameter(self::PARAM_EXPORT, self::DEFAULT_EXPORT)
        );
        
        return TRUE;
    }
    
    // ---------------------------------- <AgaviValidator OVERRIDES> -----------------------------
}

?>