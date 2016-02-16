<?php

/**
 * The Asset_Get_GetSuccessView class handle the presentation logic for our Asset/Get actions's success data.
 *
 * @version         $Id$
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
    public function executeConsole(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $msg = "Found your asset." . PHP_EOL;
        $msg .= "Asset Information: " . PHP_EOL;
        $msg .= var_export($this->getAttribute('asset_info')->toArray(), TRUE);

        $this->getResponse()->setContent($msg);
    }

    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $asset = $this->getAttribute('asset_info');
        $routing = $this->getContext()->getRouting();
        $metaData = $asset->getMetaData();
        $size = getimagesize($asset->getFullPath());

        return json_encode(array(
            'id' => $asset->getIdentifier(),
            'url' => $routing->gen('asset.thumbnail', array('aid' => $asset->getIdentifier())),
            'name' => $asset->getFullName(),
            'caption' => isset($metaData['caption']) ? $metaData['caption'] : '',
            'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
            'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
            'alt_text' => isset($metaData['alt_text']) ? $metaData['alt_text'] : '',
            'aoi' => empty($metaData['aoi']) ? NULL : $metaData['aoi'],
            'width' => isset($size[0]) ? $size[0] : '',
            'height' => isset($size[0]) ? $size[1] : '',
            'mimeType' => $asset->getMimeType(),
            'size' => $asset->getSize(),
            'meta_data' => $metaData
        ));
    }
}

?>
