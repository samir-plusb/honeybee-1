<?php

/**
 * WorkflowTicketPeer contains methods to access WorkflowTickets in the database
 *
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 02.11.2011
 *
 */
class WorkflowTicketPeer
{
    /**
     *
     * name of couchdb design document to use
     */
    const DESIGNDOC = 'designWorkflow';

    /**
     * our couchdb database connection
     *
     * @var ExtendedCouchDbClient
     */
    protected $client;

    /**
     * construct new instance.
     *
     * Do instantiate instances using the factory method in the supervisor!
     *
     * @param ExtendedCouchDbClient $client
     */
    public function __construct(ExtendedCouchDbClient $client)
    {
        $this->client = $client;
    }

    /**
     * create a ticket for a newly imported item
     *
     * @param IWorkflowItem $item
     * @return WorkflowTicket
     */
    public function createTicketByWorkflowItem(IWorkflowItem $item)
    {
        $ticket = new WorkflowTicket();
        $ticket->setWorkflowItem($item);
        $ticket->setWorkflow('_init');

        // @todo What to do if saving fails (saveTicket returns false)
        $this->saveTicket($ticket);
        return $ticket;
    }

    public function getTickets($limit = 0, $offset = 0)
    {

    }

    /**
     * store ticket in the database
     *
     * @param WorkflowTicket $ticket
     * @return boolean
     */
    public function saveTicket(WorkflowTicket $ticket)
    {
        $ticket->touch();
        $document = $ticket->toArray();
        $result = $this->client->storeDoc(NULL, $document);

        if (isset($result['ok']))
        {
            $ticket->setIdentifier($result['id']);
            $ticket->setRevision($result['rev']);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * get a ticket by its document id
     *
     * @see WorkflowTicketValidator
     *
     * @param string $identifier
     * @return WorkflowTicket
     */
    public function getTicketById($identifier, $revision = NULL)
    {
        $data = $this->client->getDoc(NULL, $identifier, $revision);
        $ticket = new WorkflowTicket($data);
        return $ticket;
    }

    /**
     * find a workflow ticket using its correpondenting import item
     *
     * @param IWorkflowItem $item
     * @return WorkflowTicket
     */
    public function getTicketByWorkflowItem(IWorkflowItem $item)
    {
        $result = $this->client->getView(NULL, self::DESIGNDOC, "ticketByWorkflowItem", array(
                'include_docs' => 'true',
                'key' => $item->getIdentifier())
        );

        if (empty($result['rows']))
        {
            return NULL;
        }
        $data = $result['rows'][0]['doc'];
        /**
         * @todo Just pass the item to the new WorkflowTicket intance.
         * 2011-18-11 7PM Thorsten Schmitt-Rink:
         * Hardsetting the workflow item directly into the couch data before hydrating
         * the workflow ticket prevents the ticket's lazy load on it's IWorkflowItem from being triggered.
         * The lack of api usage (WorkflowTicket::setWorkflowItem) is a tradeoff
         * to not complicating the ticket's hydrate for trying to be smart on the lazy load.
         */
        $data['rows'][0]['doc']['item'] = $item;
        return new WorkflowTicket($data);
    }
}

?>
