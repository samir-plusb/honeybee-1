<?php
/**
 * This interface defines the requirements of an import item in the workflow context
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
interface IWorkflowPlugin
{
    /**
     * initialize plugin
     *
     * @param WorkflowTicket $ticket workflow state
     * @param array $parameters plugn parameters as defined in the current workflow step
     * @return IWorkflowPlugin return $this for fluid interface
     */
    public function initialize(WorkflowTicket $ticket, array $parameters);

    /**
     * process the ticket
     *
     * @return WorkflowPluginResult
     */
    public function process();


    /**
     * process the ticket
     *
     * @param AgaviParameterHolder $rd parameters from a request
     * @return WorkflowPluginResult
     */
    public function processRequest(AgaviParameterHolder $rd);


    /**
     *
     * @return boolean TRUE if plugin will interact with user
     */
    public function isInteractive();
}