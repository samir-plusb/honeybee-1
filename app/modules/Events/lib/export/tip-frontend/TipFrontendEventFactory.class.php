<?php

/**
 * The TipFrontendEventFactory is responseable for creating TipFronendEvents
 * from IDataObjects, in this case restricted to EventsWorkflowItem.
 *
 * @version         $Id: TipFrontendEventFactory.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendEventFactory
{
    /**
     * Holds the name of our includeLocations property,
     * which is used to control reference expandation.
     */
    const OPT_INCLUDE_LOCATIONS = 'includeLocations';

    /**
     * Create a fresh TipFrontendEvent instance, hydrated with given EventsWorkflowItem.
     *
     * @var IDataObject $dataObject Atm only EventsWorkflowItem instance are supported.
     *
     * @return TipFrontendEvent
     */ 
    public function createFromObject(IDataObject $dataObject, array $options = array())
    {
        if (! ($dataObject instanceof EventsWorkflowItem))
        {
            throw new InvalidArgumentException(
                "The given dataObject parameter must be n instance of EventsWorkflowItem."
            );
        }

        // check if we are told to expand references to events or not.
        $includeLocations = array_key_exists(self::OPT_INCLUDE_LOCATIONS, $options) ?
            (bool)$options[self::OPT_INCLUDE_LOCATIONS] :
            TRUE;

        $exportData = array('identifier' => $dataObject->getIdentifier());

        // map all properties that need no formatting/renaming
        $directExports = array(
            'sortTitle', 'contentCreated', 'contentUpdated', 'eventSchedule',
            'category', 'subcategory', 'tickets', 'book', 'duration',
            'meetAt', 'highlight', 'duration', 'kidsInfo', 'works', 'price',
            'tags', 'ageRestriction', 'involvedPeople', 'closes', 'hasTipPoint'
        );
        $data = $dataObject->getMasterRecord()->toArray();
        foreach ($directExports as $propName)
        {
            $exportData[$propName] = isset($data[$propName]) ? $data[$propName] : NULL;
        }

        // sort articles by priority
        $articles = array();
        foreach ($data['articles'] as $article)
        {
            $articles[$article['priority']] = $article;
        }
        // get article with lowest priority
        krsort($articles);
        $article = reset($articles);
        $exportData['image'] = $this->prepareAssetData($article['pictureId']);
        // an article's text override's the event's text.
        $exportData['text'] = isset($article['text']) ? $article['text']: $data['text'];
        // an article's title override's the event's orgTitle.
        $exportData['title'] = isset($article['title']) ? 
            $article['title'] : 
            (isset($data['orgTitle']) ? $data['orgTitle'] : $data['name']);
        // format and add our filepool data.
        $exportData['filepool'] = $this->formatFilepool($data['assets']);
        // expand locations et voila, c'est fini
        if (TRUE === $includeLocations)
        {
            $exportData['locations'] = $this->buildLocationsList($dataObject);
        }

        return TipFrontendEvent::fromArray($exportData);
    }

    /**
     * Restructures our filepool to make it easier
     * to read the speicifc types and access their preview/main files.
     *
     * @param array $filepoolIn
     *
     * @return array
     */
    protected function formatFilepool(array $filepoolIn)
    {
        $filepoolOut = array('images' => array(), 'videos' => array(), 'others' => array());
        foreach ($filepoolIn as $asset)
        {
            if (2 !== count($asset)) continue; // no assets without preview and main file

            $preview = NULL;
            $main = NULL;
            if (FALSE === strpos($asset[0]['slot'], 'preview'))
            {
                $main = $asset[0];
                $preview = $asset[1];
            }
            else
            {
                $main = $asset[1];
                $preview = $asset[0];
            }

            $assetKey = 'others';
            if (FALSE !== strpos($main['mime'], 'image'))
            {
                $assetKey = 'images';
            }
            else if (FALSE !== strpos($main['mime'], 'video'))
            {
                $assetKey = 'videos';
            }

            $filepoolOut[$assetKey][] = array('preview' => $preview, 'main' => $main);
        }

        return $filepoolOut;
    }

    /**
     * Build a list of frontend location items that related to the given event item.
     *
     * @var EventsWorkflowItem $eventItem
     * @var array List of ShofiWorkflowItem
     *
     * @return array List of TipFrontendLocation.
     */
    protected function buildLocationsList(IDataObject $eventItem)
    {
        $locationFactory = new TipFrontendLocationFactory();
        $locationsList = array();
        foreach ($this->getRelatedLocations($eventItem) as $locationItem)
        {
            $locationsList[] = $locationFactory->createFromObject($locationItem, array(
                TipFrontendLocationFactory::OPT_INCLUDE_EVENTS => FALSE
            ));
        }

        return $locationsList;
    }

    /**
     * Returns a list of events that are related with given location.
     *
     * @var EventsWorkItem $eventItem
     *
     * @return array List of ShofiWorkflowItem.
     */
    public function getRelatedLocations(EventsWorkFlowItem $eventItem)
    {
        $finder = EventsFinder::create(ListConfig::fromArray(
            AgaviConfig::get('events.list_config')
        ));
        return $finder->findRelatedLocations($eventItem);
    }

    /**
     * Returns an array of meta data reflecting the assets
     * that are represented by the given list of asset ids.
     *
     * @var array $assetIds
     *
     * @return array
     */
    protected function prepareAssetData($assetId)
    {
        $assetData = NULL;

        if (NULL !== $assetId && ($asset = ProjectAssetService::getInstance()->get($assetId)))
        {
            $metaData = $asset->getMetaData();
            $imagine = new Imagine\Gd\Imagine();
            $filePath = $asset->getFullPath();
            $image = $imagine->open($filePath);
            $size = $image->getSize();
            $assetData = array(
                'data' => base64_encode(fread(fopen($filePath, 'r'), $asset->getSize())),
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

        return $assetData;
    }
}
