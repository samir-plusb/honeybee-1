<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Mvc
 */
class Workflow_GrabTicketAction extends WorkflowBaseAction
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
        $supervisor = $parameters->getParameter(WorkflowSupervisorValidator::DEFAULT_EXPORT);

        error_log(__METHOD__ . " " . get_class($supervisor));

        /* @var $ticket WorkflowTicket */
        $ticket = $parameters->getParameter('ticket');
        $this->setAttribute('ticket', $ticket);
        $user = $this->getContext()->getUser();
        $error = '';
        $reason = '';
        $translationManager = $this->getContext()->getTranslationManager();
        if (WorkflowTicket::NULL_USER === $ticket->getCurrentOwner() ||
            $ticket->getCurrentOwner() === $user->getAttribute('login'))
        {
            $ticket->setCurrentOwner($user->getAttribute('login'));

            try
            {
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
                $error = $translationManager->_('invalid_rev_text', 'workflow.errors');
                $reason = $translationManager->_('invalid_rev_title', 'workflow.errors');
            }
            catch(CouchdbClientException $e)
            {
                $error = 'Unexpected db-error while trying to grab ticket: ' . $e->getMessage();
                $reason = 'unexpected_err';
            }
        }
        else
        {
            $reason = $translationManager->_('ticket_not_avail', 'workflow.errors');
        }
        $this->setAttribute('reason', $reason);
        $this->setAttribute('error_msg', $error);
        return 'Error';
    }
}

?>
