<?php

/**
 * The Asset_PutAction takes a given asset
 * - stores the binary the filesystem
 * - saves it's meta-data to a couchdb 
 * - and returns a new id that is unqiue for the asset
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_PutAction extends AssetBaseAction
{
    /**
     * Execute the read logic for this action, hence prompt for an asset.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreStart
    {
        return 'Input';
    }
    
	/**
     * Execute the write logic for this action, hence process the given asset.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $assetUri = $parameters->getParameter(AssetResourceValidator::DEFAULT_EXPORT);
        
        $assetInfo = ProjectAssetService::getInstance()->put($assetUri);
        
        $this->setAttribute('asset_info', $assetInfo);
        
        return 'Success';
    }
}