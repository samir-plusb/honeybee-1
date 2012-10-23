<?php

/**
 * The TipFrontendLocationExport class is responseable for exporting Tip-frontend related events
 * to the correct consumers in the right structure.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendLocationExport
{
    protected $frontendRepo;

    protected $eventFactory;

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

    public function export(ShofiWorkflowItem $locationItem)
    {
        $dataSource = $locationItem->getMasterRecord()->getSource();

        if ('places-eventx' === $dataSource)
        {
            $frontendLocation = $this->locationFactory->createFromObject($locationItem);
            $this->frontendRepo->save($frontendLocation);
        }
    }

    /**
     * Export all entities that relate to the given location item.
     *
     * @var EventsWorkflowItem $eventItem
     */
    protected function exportRelatedEntities(ShofiWorkflowItem $locationItem)
    {
        $relatedDocuments = array();
        foreach ($this->locationFactory->getRelatedEvents($locationItem) as $eventItem)
        {
            $relatedDocuments[] = $this->eventFactory->createFromObject($eventItem);
        }
        $this->frontendRepo->bulkSave($relatedDocuments);
    }
}
