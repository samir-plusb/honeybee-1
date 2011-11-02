<?php
/**
 * Validator for WorkflowTicket identifier arguments
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 10.10.2011
 *
 */
class WorkflowTicketValidator extends AgaviValidator
{
    /**
     * (non-PHPdoc)
     * @see AgaviValidator::validate()
     */
    protected function validate()
    {
        $originalValue =& $this->getData($this->getArgument());

        $ticket = Workflow_SupervisorModel::getInstance()->getTicketPeer()->getTicketById($originalValue);
        if (! $ticket instanceof WorkflowTicket)
        {
            $this->throwError('instance');
            return FALSE;
        }

        $this->export($ticket, $this->getParameter('export', $this->getArgument()));
        return TRUE;
    }
}