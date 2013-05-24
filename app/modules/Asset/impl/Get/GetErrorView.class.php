<?php

/**
 * The Asset_Get_GetErrorView class handle the presentation logic for our Asset/Get actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Get_GetErrorView extends AssetBaseView
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

        $this->setAttribute('errors', $this->getErrorMessages());
        $this->setAttribute('_title', 'Asset GET - Html Interface / ERROR');
    }

    /**
     * Handle presentation logic for commandline interfaces.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeConsole(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "An arror occured while trying to retieve your asset:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getErrorMessages());

        $this->getResponse()->setContent($msg);
    }
}

?>
