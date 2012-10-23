<?php
/**
 * Validator for WorkflowTicket identifier arguments
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Agavi/Validator
 */
class WorkflowSupervisorValidator extends AgaviValidator
{
    const DEFAULT_EXPORT = 'supervisor';

    /**
     * (non-PHPdoc)
     * @see AgaviValidator::validate()
     */
    protected function validate()
    {
        $originalValue =& $this->getData($this->getArgument());

        if (! is_string($originalValue) || empty($originalValue))
        {
            $this->throwError('format');
            return FALSE;
        }

        try
        {
            $originalValue = WorkflowSupervisorFactory::createByTypeKey($originalValue);
        }
        catch(Exception $e)
        {
            $this->throwError('factory');
            return FALSE;
        }

        $this->export($originalValue, $this->getParameter('export', self::DEFAULT_EXPORT));
        return TRUE;
    }
}

?>
