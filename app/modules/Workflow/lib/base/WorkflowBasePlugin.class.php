<?php

/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
abstract class WorkflowBasePlugin implements IWorkflowPlugin
{
    /**
     *
     * @var WorkflowTicket
     */
    protected $ticket;

    /**
     *
     * @var array
     */
    protected $parameters;

    /**
     *
     * @var array
     */
    protected $gates;

    /**
     * (non-PHPdoc)
     * @see IWorkflowPlugin::initialize()
     */
    public function initialize(WorkflowTicket $ticket, array $parameters, array $gates)
    {
        $this->ticket = $ticket;
        $this->parameters = $parameters;
        $this->gates = $gates;
        return $this;
    }

    /**
     * return false to signalize a non interactive plugin by default
     *
     * @see IWorkflowPlugin::isInteractive()
     *
     * @return boolean
     */
    public function isInteractive()
    {
        return FALSE;
    }


    /**
     * get a list of gate labels
     *
     * @return array
     */
    protected function getGates()
    {
        return $this->gates;
    }
}