<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Mvc
 */
class Workflow_ProceedAction extends WorkflowBaseAction
{
    /**
     * (non-PHPdoc)
     * @see AgaviAction::getDefaultViewName()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviParameterHolder $parameters) // @codingStandardsIgnoreEnd
    {
        try
        {
            $supervisor = $parameters->getParameter(WorkflowSupervisorValidator::DEFAULT_EXPORT);
            $ticket = $parameters->getParameter('ticket');
            $gate = $parameters->getParameter('gate');
            $result = $supervisor->proceed($ticket, $gate, $this->getContainer());

            if ($result instanceof WorkflowInteractivePluginResult)
            {
                $this->setAttribute('content', $result->getResponse()->getContent());
            }
            else
            {
                $this->setAttribute('content', $result->getMessage());
            }
        }
        catch (WorkflowException $e)
        {
            $this->setAttribute(
                'content',
                'An unexpected workflow error occured while processing: ' . $e->getMessage()
            );
            $this->setAttribute('reason', $e->getCode());
            return 'Error';
        }

        return 'Success';
    }

    public function executeRead(AgaviParameterHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return $this->executeWrite($parameters);
        throw new Exception("Executing this action per read(GET) is not supported!");
    }
}

?>
