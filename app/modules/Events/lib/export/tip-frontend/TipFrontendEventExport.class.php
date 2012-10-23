<?php

/**
 * The TipFrontendEventExport class is responseable for exporting Tip-frontend related events
 * to the correct consumers in the right structure.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendEventExport
{
    /**
     * Holds the frontend repository to export incoming events to.
     *
     * @var CouchDocumentStore
     */
    protected $frontendRepo;

    /**
     * @var TipFrontendEventFactory
     */
    protected $eventFactory;

    /**
     * @var TipFrontendLocationFactory
     */
    protected $locationFactory;

    public function __construct()
    {
        $connections = AgaviConfig::get('events.connections');

        $this->eventFactory = new TipFrontendEventFactory();
        $this->locationFactory = new TipFrontendLocationFactory();

        $this->frontendRepo = new CouchDocumentStore(
            AgaviContext::getInstance()->getDatabaseConnection($connections['frontend'])
        );
    }

    /**
     * Export the given event item to the tip-frontend repo,
     * if it's source is 'events-tip'.
     *
     * @var EventsWorkflowItem $eventItem
     */
    public function export(EventsWorkflowItem $eventItem, $updateRelated = TRUE)
    {
        $source = $eventItem->getMasterRecord()->getSource();
        if ('events-tip' === $source)
        {
            $frontendEvent = $this->eventFactory->createFromObject($eventItem);
            if ($frontendEvent->hasAppointments())
            {
                $this->frontendRepo->save($frontendEvent);
    
                if ($updateRelated)
                {
                    $this->exportRelatedEntities($eventItem);
                }                
            }
        }
    }

    /**
     * Export all entities that relate to the given event item.
     *
     * @var EventsWorkflowItem $eventItem
     */
    protected function exportRelatedEntities(EventsWorkflowItem $eventItem)
    {
        $relatedDocuments = array();
        foreach ($this->eventFactory->getRelatedLocations($eventItem) as $locationItem)
        {
            $relatedDocuments[] = $this->locationFactory->createFromObject($locationItem);
        }
        $this->frontendRepo->bulkSave($relatedDocuments);
    }
}
