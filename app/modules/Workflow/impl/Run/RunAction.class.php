<?php
/**
 * Push a given workflow ticket further on through it's related workflow.
 * This can be in terms of user and/or system interaction.
 * This is the app's main entry point to executing workflows, hence processing tickets.
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Mvc
 */
class Workflow_RunAction extends ProjectBaseAction
{
    /**
     * Run read and write logic for workflow execution.
     *
     * @param AgaviParameterHolder $parameters
     *
     * @return string The name of the view to run afterwards.
     */
    public function execute(AgaviParameterHolder $parameters)
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

    /**
     * Tells whether this action requires authentication or not.
     *
     * @return bool
     */
    public function isSecure()
    {
        return FALSE;
    }
}

?>
