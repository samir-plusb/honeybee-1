<?php
/*              AUTOGENERATED CODE - DO NOT EDIT !
This base class was generated by the Dat0r library (https://github.com/berlinonline/Dat0r)
on 2013-08-05 22:31:14 and is closed to modifications by any meaning.
If you are looking for a place to alter the behaviour of 'WorkflowTicket' documents,
then the 'WorkflowTicketDocument' (skeleton) class probally might be a good place to look. */

namespace Honeybee\Domain\User\Base;

use Honeybee\Core\Dat0r;

/**
 * Serves as the base class to the 'WorkflowTicket' document skeleton.
 */
abstract class WorkflowTicketDocument extends Dat0r\WorkflowTicket
{
    /**
     * Returns an 'WorkflowTicket' document's workflowName.
     *
     * @return 
     */
    public function getWorkflowName()
    {
        return $this->getValue('workflowName');
    }

    /**
     * Sets an 'WorkflowTicket' document's workflowName.
     *
     * @param  $workflowName
     */
    public function setWorkflowName($workflowName)
    {
        $this->setValue('workflowName', $workflowName);
    }

    /**
     * Returns an 'WorkflowTicket' document's workflowStep.
     *
     * @return 
     */
    public function getWorkflowStep()
    {
        return $this->getValue('workflowStep');
    }

    /**
     * Sets an 'WorkflowTicket' document's workflowStep.
     *
     * @param  $workflowStep
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->setValue('workflowStep', $workflowStep);
    }

    /**
     * Returns an 'WorkflowTicket' document's owner.
     *
     * @return 
     */
    public function getOwner()
    {
        return $this->getValue('owner');
    }

    /**
     * Sets an 'WorkflowTicket' document's owner.
     *
     * @param  $owner
     */
    public function setOwner($owner)
    {
        $this->setValue('owner', $owner);
    }

    /**
     * Returns an 'WorkflowTicket' document's blocked.
     *
     * @return 
     */
    public function getBlocked()
    {
        return $this->getValue('blocked');
    }

    /**
     * Sets an 'WorkflowTicket' document's blocked.
     *
     * @param  $blocked
     */
    public function setBlocked($blocked)
    {
        $this->setValue('blocked', $blocked);
    }

    /**
     * Returns an 'WorkflowTicket' document's waitUntil.
     *
     * @return 
     */
    public function getWaitUntil()
    {
        return $this->getValue('waitUntil');
    }

    /**
     * Sets an 'WorkflowTicket' document's waitUntil.
     *
     * @param  $waitUntil
     */
    public function setWaitUntil($waitUntil)
    {
        $this->setValue('waitUntil', $waitUntil);
    }

    /**
     * Returns an 'WorkflowTicket' document's stepCounts.
     *
     * @return 
     */
    public function getStepCounts()
    {
        return $this->getValue('stepCounts');
    }

    /**
     * Sets an 'WorkflowTicket' document's stepCounts.
     *
     * @param  $stepCounts
     */
    public function setStepCounts($stepCounts)
    {
        $this->setValue('stepCounts', $stepCounts);
    }

    /**
     * Returns an 'WorkflowTicket' document's lastResult.
     *
     * @return 
     */
    public function getLastResult()
    {
        return $this->getValue('lastResult');
    }

    /**
     * Sets an 'WorkflowTicket' document's lastResult.
     *
     * @param  $lastResult
     */
    public function setLastResult($lastResult)
    {
        $this->setValue('lastResult', $lastResult);
    }
}