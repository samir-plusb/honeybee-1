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
        $this->saveTicket($ticket);
        return $ticket;
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
    public function getTicketById($identifier)
    {
        $data = $this->client->getDoc(NULL, $identifier);
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
        $result = $this->client->getView(
            NULL, self::DESIGNDOC, "ticketByImportitem",
            json_encode($item->getIdentifier()),
            0,
            array('include_docs' => 'true')
        );

        if (empty($result['rows']))
        {
            return $this->createTicketByWorkflowItem($item);
        }

        /**
         * @todo Just pass the item to the new WorkflowTicket intance.
         */
        $data = $result['rows'][0]['doc'];
        return new WorkflowTicket($data, $item);
    }

}