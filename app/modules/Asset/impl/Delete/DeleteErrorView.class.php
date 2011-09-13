<?php

/**
 * The Asset_Delete_DeleteErrorView class handle the presentation logic for our Asset/Delete actions's error data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Delete_DeleteErrorView extends AssetBaseView
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
        
        $this->setAttribute('errors', $this->getValidationErrorMessages());
        $this->setAttribute('_title', 'Asset DELETE - Html Interface / ERROR');
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
        $msg = "An arror occured while trying to delete your asset:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getValidationErrorMessages());
        
        $this->getResponse()->setContent($msg);
	}
}

?>