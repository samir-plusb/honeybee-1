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
                $error = $tm->_('release_ticket_error_text', 'workflow.errors');
                $reason = $tm->_('release_ticket_unex_error', 'workflow.errors');
            }
            catch(CouchdbClientException $e)
            {
                $reason = $tm->_('release_ticket_db_error', 'workflow.errors');
            }
        }
        else
        {
            $error = "The ticket is currently owned by " . $ticket->getCurrentOwner() . ", you may not release ownership of other's tickets.";
            $reason = $tm->_('ticket_not_avail', 'workflow.errors');
        }
        $this->setAttribute('reason', $reason);
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
