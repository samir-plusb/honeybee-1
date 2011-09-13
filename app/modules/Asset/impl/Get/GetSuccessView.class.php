<?php

/**
 * The Asset_Get_GetSuccessView class handle the presentation logic for our Asset/Get actions's success data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Get_GetSuccessView extends AssetBaseView
{
    /**
     * Handle presentation logic for the web  (html).
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
	public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
	{
        $this->setupHtml($parameters);
        
        $this->setAttribute('info', $this->getAttribute('asset_info')->toArray());
        $this->setAttribute('_title', 'Asset GET - Html Interface / SUCCESS');
	}
    
	/**
     * Handle presentation logic for commandline interfaces.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
	public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
	{
        $msg = "Found your asset." . PHP_EOL;
        $msg .= "Asset Information: " . PHP_EOL;
        $msg .= var_export($this->getAttribute('asset_info')->toArray(), true);
        
        $this->getResponse()->setContent($msg);
	}
}

?>