<?php

/**
 * The Asset_Thumbnail_ThumbnailErrorView class handle the presentation logic for our Asset/Thumbnail actions's error data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Thumbnail_ThumbnailErrorView extends AssetBaseView
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
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "An arror occured while trying to retieve your asset:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getErrorMessages());

        $this->getResponse()->setContent($msg);
    }
}

?>