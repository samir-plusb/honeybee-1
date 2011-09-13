<?php

/**
 * The Asset_GetAction delivers assets.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_GetAction extends AssetBaseAction
{
    /**
     * Execute the read logic for this action, hence setup asset delivery.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreStart
    {
        $assetInfo = $parameters->getParameter(AssetInfoValidator::DEFAULT_EXPORT);
        
        $this->setAttribute('asset_info', $assetInfo);
        
        return 'Success';
    }
}