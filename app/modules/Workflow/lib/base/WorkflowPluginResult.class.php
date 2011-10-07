<?php
/**
 * Return type of WorkflowPlugin::process() method as message to the workflow handler
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowPluginResult
{
    /**
     * plugin has an internal error. A message is available.
     */
    const ERROR = 0;

    /**
     * plugin has successfully processed
     */
    const OK = 1;

    /**
     * The ticket should be suspended until time
     */
    const WAIT_UNTIL = 2;

    /**
     *
     * @var integer plugins global return state
     */
    protected $state;

    /**
     *
     * @var integer offset of choosen gate in OK state. This is a 0-based index.
     */
    protected $gate;

    /**
     *
     * @var string displayable message holds a error message in error case.
     */
    protected $message;


    /**
     *
     *
     * @param integer $state
     * @param integer $gate
     * @param string $message
     */
    public function __construct($state, $gate, $message)
    {
        $this->state = intval($state);
        $this->gate = intval($gate);
        $this->message = $message;
    }

    /**
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return integer
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}