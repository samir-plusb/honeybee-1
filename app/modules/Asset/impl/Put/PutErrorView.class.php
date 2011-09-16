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
        $this->setAttribute('_title', 'Asset PUT - Html Form Interface / ERROR');
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
        $msg = "An arror occured while trying to store your asset:" . PHP_EOL;
        $msg .= '- ' . implode(PHP_EOL . '- ', $this->getErrorMessages());

        $this->getResponse()->setContent($msg);
    }

}

?>