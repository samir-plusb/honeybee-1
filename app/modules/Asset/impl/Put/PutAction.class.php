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
        $baseDir = dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR;
        $assetUri = sprintf('file://%sfoo.jpg', $baseDir);
        
        $service = new ProjectAssetService();
        $assetInfo = $service->put($assetUri);
        
        return 'Success';
    }
}