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
     * @param array $parameters plugin parameters as defined in the current workflow step
     * @param array $gates collection of gate labels
     *
     * @return IWorkflowPlugin return $this for fluid interface
     */
    public function initialize(WorkflowTicket $ticket, array $parameters, array $gates);

    /**
     * Return the plugin's unique identifier.
     * Should be human readable and use underscore notation like for example: delete_newsitem
     *
     * @return string
     */
    public function getPluginId();

    /**
     * process the ticket
     *
     * @return WorkflowPluginResult
     */
    public function process();

    /**
     *
     * @return boolean TRUE if plugin will interact with user
     */
    public function isInteractive();
}

?>
