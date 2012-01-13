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
        if (WorkflowTicket::NULL_USER === $ticket->getCurrentOwner() || $ticket->getCurrentOwner() === $user->getAttribute('name'))
        {
            $ticket->setCurrentOwner($user->getAttribute('name'));

            try
            {
                $supervisor = Workflow_SupervisorModel::getInstance();
                if ($supervisor->getTicketPeer()->saveTicket($ticket))
                {
                    return 'Success';
                }
                $error = 'Failed to grab ticket. Revision is not up to date.';
            }
            catch(CouchdbClientException $e)
            {
                $error = 'Unexpected db-error while trying to grab ticket: ' . $e->getMessage();
            }
        }
        else
        {
            $error = "The ticket is allready owned by " . $ticket->getCurrentOwner();
        }
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
