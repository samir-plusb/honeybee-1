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
        $result = $this->processTicket($parameters);
        $viewName = 'Input';
        if ($result instanceof WorkflowInteractivePluginResult)
        {
            $this->setAttribute('response', $result->getResponse());
        }
        return $viewName;
    }

    /**
     * process the ticket
     *
     * @return AgaviExecutionContainer
     */
    protected function processTicket(AgaviRequestDataHolder $parameters)
    {
        $ticket = $parameters->getParameter('ticket');
        $supervisor = Workflow_SupervisorModel::getInstance();
        $result = $supervisor->processTicket($ticket, $this->getContainer());
        return $result;
    }
}

?>
