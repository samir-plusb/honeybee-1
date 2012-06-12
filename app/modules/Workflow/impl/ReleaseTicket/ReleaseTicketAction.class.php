<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Mvc
 */
class Workflow_ReleaseTicketAction extends WorkflowBaseAction
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
        $supervisor = $parameters->getParameter(WorkflowSupervisorValidator::DEFAULT_EXPORT);
        /* @var $ticket WorkflowTicket */
        $ticket = $parameters->getParameter('ticket');
        $this->setAttribute('ticket', $ticket);
        $user = $this->getContext()->getUser();
        $error = '';
        $reason = '';
        $translationManager = $this->getContext()->getTranslationManager();
        if ($ticket->getCurrentOwner() === $user->getAttribute('login'))
        {
            try
            {
                $ticket->setCurrentOwner(WorkflowTicket::NULL_USER);
                
                if ($supervisor->getWorkflowTicketStore()->save($ticket))
                {
                    $item = $supervisor->getWorkflowItemStore()->fetchByIdentifier($ticket->getItem());
                    $supervisor->getWorkflowItemStore()->save(
                        $item->setCurrentState(array(
                            'workflow' => $ticket->getWorkflow(),
                            'step'     => $ticket->getCurrentStep(),
                            'owner'    => $ticket->getCurrentOwner()
                        ))
                    );
                    $supervisor->getWorkflowItemStore()->save($item);
                    return 'Success';
                }
                $error = $translationManager->_('release_ticket_error_text', 'workflow.errors');
                $reason = $translationManager->_('release_ticket_unex_error', 'workflow.errors');
            }
            catch(CouchdbClientException $e)
            {
                $reason = $translationManager->_('release_ticket_db_error', 'workflow.errors');
                $error = $e->getMessage();
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
