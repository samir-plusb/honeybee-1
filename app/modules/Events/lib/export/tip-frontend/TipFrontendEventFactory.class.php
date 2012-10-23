<?php

/**
 * The TipFrontendEventFactory is responseable for creating TipFronendEvents
 * from IDataObjects, in this case restricted to EventsWorkflowItem.
 *
 * @version         $Id$
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

	public static $ranking = array(
        'Musik & Party' => 1,
        'Kino & Film' => 2,                        
        'Kultur & Freizeit' => 3,
		'Sonstiges' => 4,		
	);

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
                "The given dataObject parameter must be an instance of EventsWorkflowItem."
            );
        }

        // check if we are told to expand references to events or not.
        $includeLocations = array_key_exists(self::OPT_INCLUDE_LOCATIONS, $options) ?
            (bool)$options[self::OPT_INCLUDE_LOCATIONS] :
            TRUE;

        $exportData = array('identifier' => $dataObject->getIdentifier());

        // map all properties that need no formatting/renaming
        $directExports = array(
            'sortTitle', 'contentCreated', 'contentUpdated',
            'subcategory', 'tickets', 'book', 'duration',
            'meetAt', 'duration', 'kidsInfo', 'works', 'price',
            'tags', 'ageRestriction', 'involvedPeople', 'closes'
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

		$exportData['category'] = array(
			'name' => $data['category'],
			'priority' => isset(self::$ranking[$data['category']])?self::$ranking[$data['category']]:99,
		);
	
		$exportData['highlight'] = (TRUE === $data['highlight'] || TRUE === $data['hasTipPoint']);

        $exportData['eventSchedule'] = $this->buildEventSchedule($dataObject);
 
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
     * Removes old appointments
     *
     * @var EventsWorkflowItem $eventItem
     * @var array List of ShofiWorkflowItem
     *
     * @return array List of Appointments.
     */
    protected function buildEventSchedule(IDataObject $eventItem)
    {
        $data = $eventItem->getMasterRecord()->toArray();
        // ignore everything thats older than today 0:00
        $currentTimestamp = strtotime('today 0:00');
        foreach($data['eventSchedule'] as $locationsKey => &$locations)
        {
            foreach ($locations as $locationKey => &$location)
            {
                foreach ($location['appointments'] as $appointmentKey => $appointment)
                {
                    if (
                            // If startdate is in the past and we've no enddate (means appointment is over)
                            (strtotime($appointment['startDate']) < $currentTimestamp && empty($appointment['endDate'])) || 
                            // If both, startdate and enddate are in the past
                            (strtotime($appointment['startDate']) < $currentTimestamp && strtotime($appointment['endDate']) < $currentTimestamp))
                    {
                        //echo 'Deleted appointment ' . $appointment['startDate'] . ' - ' . $appointment['endDate'] . PHP_EOL;
                        unset($location['appointments'][$appointmentKey]);
                    }
                }
                if (empty($location['appointments']))
                {
                    unset($locations[$locationKey]);
                }
            }
            if (empty($locations))
            {
                unset($data['eventSchedule'][$locationsKey]);
            }
        }
        //echo print_r($data['eventSchedule'],1) . PHP_EOL;
        return $data['eventSchedule'];
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
