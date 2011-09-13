<?php

/**
 * The Asset_Delete_DeleteSuccessView class handle the presentation logic for our Asset/Delete actions's success data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Delete_DeleteSuccessView extends AssetBaseView
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
        $this->setAttribute('_title', 'Asset DELETE - Html Interface / SUCCESS');
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
        $msg = "Successfully deleted your asset from path:" . PHP_EOL;
        $msg .= $this->getAttribute('asset_info')->getFullPath();
        
        $this->getResponse()->setContent($msg);
	}
}

?>