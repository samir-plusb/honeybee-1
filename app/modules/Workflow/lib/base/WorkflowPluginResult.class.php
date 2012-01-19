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
class WorkflowPluginResult implements IWorkflowPluginResult
{
    /**
     * @var integer plugin result state; use our STATE_ constants
     */
    private $state;

    /**
     * @var string Name of the gate to use when existing this step.
     */
    private $gate;

    /**
     *
     * @var string message for the user
     */
    private $message;

    private $frozen = FALSE;

    public function freeze()
    {
        $this->frozen = TRUE;
    }

    protected function verifyMutability()
    {
        if (TRUE === $this->frozen)
        {
            throw new WorkflowException("Trying to modify a non-mutable (frozen) workflow-plugin result!");
        }
    }

    /**
     * Returns an array representation of the location.
     *
     * @return string
     */
    public function toArray()
    {
        $props = array('state', 'gate', 'message');
        $data = array();
        foreach ($props as $prop)
        {
            $getter = 'get' . ucfirst($prop);
            $data[$prop] = $this->$getter();
        }
        return $data;
    }

    public static function fromArray(array $data)
    {
        if (! isset($data['state']))
        {
            throw new WorkflowException(
                "When creating new plugin results from given data, the state information is considered mandatory." .
                "The given data is missing one of the latter values."
            );
        }

        $result = new self();
        if (isset($data['gate']))
        {
            $result->setGate($data['gate']);
        }
        $result->setState($data['state']);
        $result->setMessage(isset($data['message']) ? $data['message'] : NULL);
        $result->freeze();

        return $result;
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

    public function setState($state)
    {
        $this->verifyMutability();
        $this->state = intval($state);
    }

    /**
     * Returns the name of the gate to use to navigate on to the next workflow destination.
     *
     * @return string
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * Set the name of the result's gate.
     *
     * @param string $gate
     */
    public function setGate($gate)
    {
        if (! is_string($gate))
        {
            throw new InvalidArgumentException(
                "Only strings may be provided when referring to workflow gates (e.g. gate-names)." . PHP_EOL .
                "Lookup the name of the desired gate in your workflow's definition. Given value -> " . print_r($gate, TRUE)
            );
        }
        $this->verifyMutability();
        $this->gate = $gate;
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

    public function setMessage($message)
    {
        $this->verifyMutability();
        $this->message = $message;
    }

    /**
     * check if ticket can step forward in workflow
     *
     * @return boolean
     */
    public function canProceedToNextStep()
    {
        return self::STATE_OK == $this->state && NULL !== $this->gate;
    }


    /**
     * convert instance to string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s(State %d, Gate %d, Message: "%s")',
            get_class($this), $this->state, $this->gate, $this->message
        );
    }
}

?>
