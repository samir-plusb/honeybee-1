<?php

/**
 * The base view from which the Api_NextItem* and the Api_PrevItem* views derive from.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class BrowseNewsBaseView extends NewsBaseView
{
    /**
     * Handle presentation logic for json requests.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $item = $this->getAttribute('item');
        $itemData = NULL;
        $ticketData = NULL;
        if ($item)
        {
            $itemData = $item->toArray();
            $importData = &$itemData['importItem'];
            $importData['assets'] = $this->prepareAssets($item->getImportItem());
            $ticketData = $this->getAttribute('ticket')->toArray();
        }

        $this->getResponse()->setContent(
            json_encode(
                array('state' => 'ok', 'item' => $itemData, 'ticket' => $ticketData)
            )
        );
    }

    /**
     * Transform any assets attached to the given import item into an assoc array.
     *
     * @param IImportItem $item
     *
     * @return array
     */
    protected function prepareAssets(IImportItem $item)
    {
        $assetService = ProjectAssetService::getInstance();
        $assets = array();
        $ro = $this->getContext()->getRouting();
        foreach ($item->getMedia() as $mediaId)
        {
            $asset = $assetService->get($mediaId);
            $assetData = $asset->toArray();
            $metaData = $assetData['metaData'];
            $assetCaption = isset($metaData['caption']) ? htmlspecialchars($metaData['caption']) : 'No Title';
            $assets[] = array(
                'id' => $mediaId,
                'url' => $ro->gen('asset.binary', array('aid' => $mediaId)),
                'caption' => sprintf('%s (%s)', $asset->getFullName(), $assetCaption),
                'altText' => isset($metaData['alt']) ? htmlspecialchars($metaData['alt']) : $assetCaption
            );
        }
        return $assets;
    }
}

?>
