<?php

/**
 * The Asset_Put_PutSuccessView class handle the presentation logic for our Asset/Put actions's success data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_Put_PutSuccessView extends AssetBaseView
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
        $this->setAttribute('_title', 'Asset PUT - Html Form Interface / SUCCESS');
    }

    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $asset = $this->getAttribute('asset_info');
        $routing = $this->getContext()->getRouting();
        $data = $asset->toArray();
        ksort($data['metaData']);
        $data['url'] = $routing->gen('asset.thumbnail', array('aid' => $asset->getIdentifier()));
        $this->getResponse()->setContent(json_encode(array(
            'state' => 'ok',
            'data' => $data
        )));
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
        $msg = "Successfully stored your asset." . PHP_EOL;
        $msg .= "Asset Information: " . PHP_EOL;
        $msg .= var_export($this->getAttribute('asset_info')->toArray(), TRUE);

        $this->getResponse()->setContent($msg);
    }
}

?>
