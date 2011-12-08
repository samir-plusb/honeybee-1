<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_Run_RunInputView extends ProjectBaseView
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
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        return $this->processTicket($parameters)->getContent();
    }

    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeText()
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        return $this->processTicket($parameters)->getContent();
    }

    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        return $this->processTicket($parameters)->getContent();
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
