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
        $revision = NULL;
        $identifier = NULL;

        if (TRUE === $this->getParameter('validate_revision'))
        {
            if (! is_array($originalValue))
            {
                $this->throwError('value_structure');
                return FALSE;
            }
            if (isset($originalValue['id']) && isset($originalValue['rev']))
            {
                $identifier = $originalValue['id'];
                $revision = $originalValue['rev'];
            }
            else
            {
                $this->throwError('value_structure');
                return FALSE;
            }
        }
        else
        {
            $identifier = $originalValue;
        }
        $ticket = Workflow_SupervisorModel::getInstance()->getTicketPeer()->getTicketById($identifier, $revision);
        if (! $ticket instanceof WorkflowTicket)
        {
            $this->throwError('instance');
            return FALSE;
        }

        $this->export($ticket, $this->getParameter('export', $this->getArgument()));
        return TRUE;
    }
}

?>
