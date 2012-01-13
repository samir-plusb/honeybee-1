<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: Workflow_GrabTicketAction.class.php -1   $
 * @package Workflow
 */
class Workflow_ReleaseTicketAction extends ProjectBaseAction
{
    /**
     * (non-PHPdoc)
     * @see AgaviAction::getDefaultViewName()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviParameterHolder $parameters) // @codingStandardsIgnoreEnd
    {
        /* @var $ticket WorkflowTicket */
        $ticket = $parameters->getParameter('ticket');
        $this->setAttribute('ticket', $ticket);
        $user = $this->getContext()->getUser();
        $error = '';
        $reason = '';
        if ($ticket->getCurrentOwner() === $user->getAttribute('login'))
        {
            $ticket->setCurrentOwner(WorkflowTicket::NULL_USER);
            try
            {
                $supervisor = Workflow_SupervisorModel::getInstance();
                if ($supervisor->getTicketPeer()->saveTicket($ticket))
                {
                    return 'Success';
                }
                $error = 'Failed to release ticket. Ticket persistance unexpectedly failed.';
                $reason = 'storage_err';
            }
            catch(CouchdbClientException $e)
            {
                $error = 'Unexpected db-error while trying to release ticket: ' . $e->getMessage();
                $reason = 'unexpected_err';
            }
        }
        else
        {
            $error = "The ticket is currently owned by " . $ticket->getCurrentOwner() . ", you may not release ownership of other's tickets.";
            $reason = 'ticket_not_avail';
        }
        $this->setAttribute('reason', $reason);
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
