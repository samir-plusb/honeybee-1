<?php

/**
 * The TipFrontendLocationFactory is responseable for creating TipFronendEventLocations
 * from IDataObjects, in this case restricted to ShofiWorkflowItems.
 *
 * @version         $Id: TipFrontendLocationFactory.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendLocationFactory
{
    /**
     * Holds the name of our includeEvents property,
     * which is used to control reference expandation.
     */
    const OPT_INCLUDE_EVENTS = 'includeEvents';

    /**
     * Create a fresh TipFrontendLocation instance, hydrated with given ShofiWorkflowItem.
     *
     * @var IDataObject $dataObject.
     *
     * @return TipFrontendLocation
     */ 
    public function createFromObject(IDataObject $dataObject, array $options = array())
    {
        if (! ($dataObject instanceof ShofiWorkflowItem))
        {
            throw new InvalidArgumentException(
                "The given dataObject parameter must be n instance of ShofiWorkflowItem."
            );
        }

        // check if we are told to expand references to events or not.
        $includeEvents = array_key_exists(self::OPT_INCLUDE_EVENTS, $options) ?
            (bool)$options[self::OPT_INCLUDE_EVENTS] :
            TRUE;

        // define mappings from backend properties to frontend properties...
        $data = $dataObject->toArray();
        $exportDataKeys = array(
            'identifier' => 'identifier',
            'coreItem' => 'coreData',
            'salesItem' => 'salesData',
            'detailItem' => 'detailData',
            'lastModified' => 'lastModified'
        );
        $data['detailItem']['attachments'] = $this->prepareContentMachineAssetData(
            $dataObject->getDetailItem()->getAttachments()
        );
        $data['salesItem']['attachments'] = $this->prepareContentMachineAssetData(
            $dataObject->getSalesItem()->getAttachments()
        );
        $attributes = $dataObject->getAttributes();
        $data['category'] = $attributes['tip-category'];
        $data['subcategory'] = $attributes['tip-subcategory'];
        $data['publicTransports'] = isset($attributes['public-transports']) ? $attributes['public-transports'] : array();

        // ... then actually map the data to the frontend structure
        $exportData = array();
        foreach ($exportDataKeys as $localKey => $exportKey)
        {
            $exportData[$exportKey] = $data[$localKey];
        }
        // expand references et voîla, c'est fini.
        if (TRUE === $includeEvents)
        {
            $exportData['events'] = $this->buildEventsList($dataObject);
        }

        return TipFrontendLocation::fromArray($exportData);
    }

    /**
     * Returns a list of events that are related with given location.
     *
     * @var ShofiWorkflowItem $locationItem
     *
     * @return array List of EventsWorkflowItem.
     */
    public function getRelatedEvents(ShofiWorkflowItem $locationItem)
    {
        $finder = EventsFinder::create(ListConfig::fromArray(
            AgaviConfig::get('events.list_config')
        ));
        $result = $finder->find(ListState::fromArray(array(
            'offset' => 0,
            'limit' => 500,
            'filter' => array(
                'masterRecord.eventSchedule.locations.locationId' => $locationItem->getIdentifier()
            )
        )));
        return $result->getItems();
    }

    /**
     * Build a list of expanded event items that related to the given location item.
     *
     * @var ShofiWorkflowItem $locationItem
     * @var array List of EventsWorkflowItem
     *
     * @return array List of TipFrontendEvent.
     */
    protected function buildEventsList(IDataObject $locationItem)
    {
        $eventFactory = new TipFrontendEventFactory();
        $eventsList = array();
        foreach ($this->getRelatedEvents($locationItem) as $eventItem)
        {
            $eventsList[] = $eventFactory->createFromObject($eventItem, array(
                TipFrontendEventFactory::OPT_INCLUDE_LOCATIONS => FALSE
            ));
        }

        return $eventsList;
    }

    /**
     * Returns an array of meta data reflecting the assets
     * that are represented by the given list of asset ids.
     *
     * @var array $assetIds
     *
     * @return array
     */
    protected function prepareContentMachineAssetData(array $assetIds)
    {
        $assets = array();
        $assetService = ProjectAssetService::getInstance();
        foreach ($assetService->multiGet($assetIds) as $id => $asset)
        {
            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assets[] = array(
                'id' => $asset->getIdentifier(),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
                'mime' => $asset->getMimeType(),
                'filename' => $asset->getFullName(),
                'modified' => date(DATE_ISO8601, filemtime($filePath)),
                'copyright' => isset($metaData['copyright']) ? $metaData['copyright'] : '',
                'copyright_url' => isset($metaData['copyright_url']) ? $metaData['copyright_url'] : '',
                'caption' => isset($metaData['caption']) ? $metaData['caption'] : ''
            );
        }

        return $assets;
    }
}
