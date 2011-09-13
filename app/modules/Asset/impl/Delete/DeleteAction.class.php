<?php

/**
 * The Asset_DeleteAction deletes assets.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_DeleteAction extends AssetBaseAction
{
    /**
     * Execute the read logic for this action, hence delete that asset.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreStart
    {
        $assetInfo = $parameters->getParameter(AssetInfoValidator::DEFAULT_EXPORT);
        $this->setAttribute('asset_info', $assetInfo);
        
        if (!ProjectAssetService::getInstance()->delete($assetInfo->getId()))
        {
            return 'Error';
        }
        
        return 'Success';
    }
}