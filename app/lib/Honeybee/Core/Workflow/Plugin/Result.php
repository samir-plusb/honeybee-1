<?php

namespace Honeybee\Core\Workflow\Plugin;

use Honeybee\Core\Workflow\Exception;
use BaseDataObject;

/**
 * The Result is a base implementation of the IResult interface
 * and should be extended by other concrete IResult implementations.
 * It fully implements the interface and should be used by all plugins that are not interactive.
 *
 * @author tay
 */
class Result extends BaseDataObject implements IResult
{
    /**
     * Holds a int flag that reflects the result of a plugins process method invocation.
     * Only our STATE_* constants are valid values.
     *
     * @var integer
     */
    private $state;

    /**
     * The name of the plugin gate that is adressed by the plugin result..
     *
     * @var string
     */
    private $gate;

    /**
     * Holds a message related to the execution of the plugin
     * which's result we are reflecting.
     *
     * @var string
     */
    private $message;

    /**
     * Holds a flag that is used to tell whether we are closed to modification or not.
     * Make sure to freeze your results once you are sure about the result.
     *
     * @var boolean
     */
    private $frozen = FALSE;

    /**
     * Create a new Result instance from the given data.
     *
     * @param array $data
     *
     * @return Result
     */
    public static function fromArray(array $data = array())
    {
        $result = new self($data);
        $result->freeze();
        return $result;
    }

    /**
     * Freeze the current plugin result,
     * which means modifications will not be allowed from now on.
     */
    public function freeze()
    {
        $this->frozen = TRUE;
    }

    /**
     * Return the state of the plugin result.
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the state of the plugin result.
     *
     * @param int $state
     */
    public function setState($state)
    {
        $this->verifyMutability();
        $this->state = intval($state);
    }

    /**
     * Returns the name of the gate to use when processing the result
     * to determine the next workflow action.
     *
     * @return string
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * Set the name of the plugin result's gate.
     *
     * @param string $gate
     */
    public function setGate($gate)
    {
        if (! is_string($gate))
        {
            throw new InvalidArgumentException(sprintf(
                "Only strings may be provided when referring to workflow gates (e.g. gate-names).\n" .
                "Lookup the name of the desired gate in your workflow's definition. Given value -> %s",
                print_r($gate, TRUE)
            ));
        }
        $this->verifyMutability();
        $this->gate = $gate;
    }

    /**
     * Return the plugin result's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the name of the plugin result's message.
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->verifyMutability();
        $this->message = $message;
    }

    /**
     * Check if ticket can step forward in workflow.
     *
     * @return boolean
     */
    public function canProceedToNextStep()
    {
        return self::STATE_OK == $this->state && NULL !== $this->gate;
    }

    /**
     * Verify that the result is open for modification.
     *
     * @throws Exception If the plugin result has been frozen.
     */
    protected function verifyMutability()
    {
        if (TRUE === $this->frozen)
        {
            throw new Exception("Trying to modify a non-mutable (frozen) workflow-plugin result!");
        }
    }

    /**
     * Return a list of properties to exclude from
     * our toArray and fromArray methods.
     * 
     * @return array
     */
    protected function getPropertyBlacklist()
    {
        return array_merge(
            parent::getPropertyBlacklist(),
            array('frozen')
        );
    }
}
