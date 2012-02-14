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
     * Execute the action's read logic, hence release ownership of the given ticket(id).
     *
     * @param AgaviParameterHolder $parameters
     *
     * @return string
     */
    public function executeRead(AgaviParameterHolder $parameters)
    {
        /* @var $ticket WorkflowTicket */
        $ticket = $parameters->getParameter('ticket');
        $this->setAttribute('ticket', $ticket);
        $user = $this->getContext()->getUser();
        $error = '';
        $reason = '';
        $translationManager = $this->getContext()->getTranslationManager();
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
                $error = $translationManager->_('release_ticket_error_text', 'workflow.errors');
                $reason = $translationManager->_('release_ticket_unex_error', 'workflow.errors');
            }
            catch(CouchdbClientException $e)
            {
                $reason = $translationManager->_('release_ticket_db_error', 'workflow.errors');
            }
        }
        else
        {
            $error = sprintf(
                "The ticket is currently owned by %s, you may not release ownership of other's tickets.",
                $ticket->getCurrentOwner()
            );
            $reason = $translationManager->_('ticket_not_avail', 'workflow.errors');
        }
        $this->setAttribute('reason', $reason);
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
