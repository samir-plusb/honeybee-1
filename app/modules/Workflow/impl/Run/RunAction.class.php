<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_RunAction extends ProjectBaseAction
{
    /**
     * (non-PHPdoc)
     * @see AgaviAction::getDefaultViewName()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function execute(AgaviParameterHolder $parameters) // @codingStandardsIgnoreEnd
    {
        try
        {
            $ticket = $parameters->getParameter('ticket');
            $supervisor = Workflow_SupervisorModel::getInstance();
            $result = $supervisor->processTicket($ticket, $this->getContainer());
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
}

?>
