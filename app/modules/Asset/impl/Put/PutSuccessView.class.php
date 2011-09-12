<?php

/**
 * The Asset_Put_PutSuccessView class handle the presentation logic for our Asset/Put actions's success data.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Put_PutSuccessView extends AssetBaseView
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
        $this->getResponse()->setContent("Successfully stored your asset.");
	}
}

?>