<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: Workflow_GrabTicketAction.class.php -1   $
 * @package Workflow
 */
class Workflow_GrabTicketAction extends ProjectBaseAction
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
        if (WorkflowTicket::NULL_USER === $ticket->getCurrentOwner() || $ticket->getCurrentOwner() === $user->getAttribute('login'))
        {
            $ticket->setCurrentOwner($user->getAttribute('login'));

            try
            {
                $supervisor = Workflow_SupervisorModel::getInstance();
                if ($supervisor->getTicketPeer()->saveTicket($ticket))
                {
                    return 'Success';
                }
                $error = 'Failed to grab ticket. Revision is not up to date.';
                $reason = 'invalid_rev';
            }
            catch(CouchdbClientException $e)
            {
                $error = 'Unexpected db-error while trying to grab ticket: ' . $e->getMessage();
                $reason = 'unexpected_err';
            }
        }
        else
        {
            $error = "The ticket is allready owned by " . $ticket->getCurrentOwner();
            $reason = 'ticket_not_avail';
        }
        $this->setAttribute('reason', $reason);
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
