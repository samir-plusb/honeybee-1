<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: ProceedAction.class.php 679 2012-01-09 17:23:50Z tschmitt $
 * @package Workflow
 */
class Workflow_ProceedAction extends ProjectBaseAction
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
            $ticket = $parameters->getParameter('ticket');
            $gate = $parameters->getParameter('gate');
            $supervisor = Workflow_SupervisorModel::getInstance();
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
