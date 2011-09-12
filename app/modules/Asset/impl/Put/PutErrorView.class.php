<?php

/**
 * The Asset_Put_PutSuccessView class handle the presentation logic for our Asset/Put actions's error data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Put_PutErrorView extends AssetBaseView
{
	/**
     * Handle presentation logic for commandline interfaces.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
	public function executeText(AgaviRequestDataHolder $rd) // @codingStandardsIgnoreEnd
	{
        $this->getResponse()->setContent("An arror occured while trying to store your asset.");
	}
}

?>