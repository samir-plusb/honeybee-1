<?php
/**
 * Validator for WorkflowTicket identifier arguments
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Agavi/Validator
 */
class WorkflowTicketValidator extends AgaviValidator
{
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
        $ticketType = $this->getParameter('type', FALSE);
        $typeArgName = $this->getParameter('type_argument', 'type');
        $ticketType = $ticketType ? $ticketType : $this->getData($typeArgName);
        if (! $ticketType)
        {
            throw new InvalidArgumentException("Missing ticket type for workflowticket validator.");
        }
        $ticketStore = WorkflowSupervisorFactory::createByTypeKey($ticketType)->getWorkflowTicketStore();
        $ticket = $ticketStore->fetchByIdentifier($identifier, $revision);
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
