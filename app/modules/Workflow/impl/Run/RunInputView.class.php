<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_Run_RunInputView extends ProjectWorkflowBaseView
{


    /**
     * Handles the Html output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeHtml(AgaviRequestDataHolder $rd)
    {
        $this->setupHtml($rd);
        $result = $this->processTicket($rd);
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeText()
     */
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $result = $this->processTicket($rd);
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        $result = $this->processTicket($rd);
        return $result;
    }

    /**
     * process the ticket
     *
     * @return AgaviExecutionContainer
     */
    protected function processTicket(AgaviRequestDataHolder $rd)
    {
        $ticket = $rd->getParameter('ticket');

        $supervisor = Workflow_SupervisorModel::getInstance();
        $result = $supervisor->processTicket($ticket, $this->getContainer());
        return ($result instanceof AgaviExecutionContainer) ? $result : NULL;
    }
}
