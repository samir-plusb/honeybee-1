<?php

/**
 * holds the result of a plugin process method call
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 21.10.2011
 *
 */
class WorkflowPluginResult
{
    /**
     * plugin has an internal error. A message is available.
     */
    const STATE_ERROR = 0;

    /**
     * plugin has successfully processed
     */
    const STATE_OK = 1;

    /**
     * The ticket should be suspended until time
     */
    const STATE_WAIT_UNTIL = 2;

    /**
     * The ticket should be suspended until interactive request
     */
    const STATE_EXPECT_INPUT = 3;

    /**
     * default gate number when to stop the workflow
     */
    const GATE_NONE = -1;

    /**
     * default gate number for simple plugins (first gate, gate 0)
     */
    const GATE_DEFAULT = 0;

    /**
     * @var integer plugin result state; use our STATE_ constants
     */
    private $state;

    /**
     * @var integer gate number to the next workflow step
     */
    private $gate;

    /**
     *
     * @var string message for the user
     */
    private $message;

    /**
     *
     *
     * @param integer $state
     * @param integer $gate
     * @param string $message
     */
    public function __construct($state, $gate = self::GATE_NONE, $message = NULL)
    {
        $this->state = intval($state);
        $this->gate = intval($gate);
        $this->message = $message;
    }


    /**
     * construct result from array
     *
     * @param array $data assoziative array of member data
     * @return WorkflowPluginResult
     */
    public static function fromArray(array $data)
    {
        return new self(
            array_key_exists('state', $data) ? $data['state'] : self::STATE_ERROR,
            array_key_exists('gate', $data) ? $data['gate'] : self::GATE_NONE,
            array_key_exists('message', $data) ? $data['message'] : NULL);
    }


    /**
     * provide our member data as array as base for json export
     *
     * @return array
     */
    public function toArray()
    {
        return array_filter(get_object_vars($this));
    }

    /**
     * gets plugin result state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * gets the gate number to the next workflow step
     *
     * @return integer
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * gets the message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * check if ticket can step forward in workflow
     *
     * @return boolean
     */
    public function canProceedToNextStep()
    {
        return self::STATE_OK == $this->state && self::GATE_DEFAULT <= $this->gate;
    }


    /**
     * convert instance to string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(__CLASS__.'(State %d, Gate %d, Message: "%s")', $this->state, $this->gate, $this->message);
    }
}