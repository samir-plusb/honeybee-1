<?php

/**
 * The WorkflowTicketStore provides access to the ticket store (save/retrieve/delete/factory).
 * It extends the CouchDocumentStore to add in an additional factory
 * and an additional retrieval method to lookup tickets based on a given workflow item.
 *
 * @author          tay
 * @version         $Id$
 * @package         Workflow
 * @subpackage      Ticket
 */
class WorkflowTicketStore extends CouchDocumentStore
{
    /**
     * The name of couchdb design document to used to get tickets by workflow item.
     */
    const DESIGNDOC = 'tickets';

    /**
     * Find a workflow ticket by workflow item.
     *
     * @param IWorkflowItem $item The workflow item to use when looking up the ticket.
     *
     * @return WorkflowTicket The ticket related to the given workflowitem or NULL if none was found.
     */
    public function getTicketByWorkflowItem(IWorkflowItem $item)
    {
        $result = $this->client->getView(NULL, self::DESIGNDOC, "byWorkflowItem", array(
            'include_docs' => 'true',
            'key' => $item->getIdentifier())
        );
        if (empty($result['rows']))
        {
            return NULL;
        }
        return self::factory($result['rows'][0]['doc']);
    }

    /**
     * Create a workflow ticket for the given workflow item.
     *
     * @param IWorkflowItem $item
     *
     * @return WorkflowTicket
     */
    public function createTicketByWorkflowItem(IWorkflowItem $item)
    {
        $ticket = WorkflowTicket::fromArray();
        $ticket->setItem($item);
        $ticket->setWorkflow($item->determineWorkflow());
        // @todo What to do if saving fails (saveTicket returns false)
        if (! $this->save($ticket))
        {
            throw new Exception("Failed saving ticket.");
        }
        return $ticket;
    }
}

?>
