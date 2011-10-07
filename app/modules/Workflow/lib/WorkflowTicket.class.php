<?php
/**
 * A ticket holds the state of one content item in the the associated workflow.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowTicket implements Serializable
{
    /**
     * The ticket belongs to this workflow
     *
     * @var WorkflowHandler
     */
    protected $workflow;


    /**
     * @var string identifier of current workflow step
     */
    protected $currentStep;

    /**
     * The administrated content item
     *
     * @var          IImportItem
     */
    protected $importItem;

    /**
     * the tickets lock status
     *
     * @var          boolean
     */
    protected $blocked;

    /**
     * @todo fill in documentation here
     *
     * @var          AgaviUser
     */
    protected $currentOwner;

    /**
     * @todo fill in documentation here
     *
     * @var          DateTime
     */
    protected $waitUntil;

    /**
     * Sets the workflow attribute.
     *
     * @param        Workflow the new value for workflow
     *
     * @return       void
     */
    public function setWorkflow(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Retrieves the workflow attribute.
     *
     * @return       Workflow the value for workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Sets the currentStep attribute.
     *
     * @param        string the new value for currentStep
     *
     * @return       void
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;
    }

    /**
     * Retrieves the currentStep attribute.
     *
     * @return       string the value for currentStep
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * Sets the importItem attribute.
     *
     * @param        IImportItem the new value for importItem
     *
     * @return       void
     */
    public function setImportItem(IImportItem $importItem)
    {
        $this->importItem = $importItem;
    }

    /**
     * Retrieves the importItem attribute.
     *
     * @return       IImportItem the value for importItem
     */
    public function getImportItem()
    {
        return $this->importItem;
    }

    /**
     * Sets the blocked attribute.
     *
     * @param        boolean the new value for blocked
     *
     * @return       void
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;
    }

    /**
     * Retrieves the blocked attribute.
     *
     * @return       boolean the value for blocked
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Sets the currentOwner attribute.
     *
     * @param        AgaviUser the new value for currentOwner
     *
     * @return       void
     */
    public function setCurrentOwner(AgaviUser $currentOwner)
    {
        $this->currentOwner = $currentOwner;
    }

    /**
     * Retrieves the currentOwner attribute.
     *
     * @return       AgaviUser the value for currentOwner
     */
    public function getCurrentOwner()
    {
        return $this->currentOwner;
    }

    /**
     * Sets the waitUntil attribute.
     *
     * @param        DateTime the new value for waitUntil
     *
     * @return       void
     */
    public function setWaitUntil(DateTime $waitUntil = NULL)
    {
        $this->waitUntil = $waitUntil;
    }

    /**
     * Sets the waitUntil attribute.
     *
     * @param string $iso8601 the new value for waitUntil in Iso8601 format
     */
    public function setWaitUntilFromIso8601($iso8601)
    {
        $this->setWaitUntil(empty($iso8601) ? NULL : new DateTiem($iso8601));
    }

    /**
     * Retrieves the waitUntil attribute.
     *
     * @return       DateTime the value for waitUntil
     */
    public function getWaitUntil()
    {
        return $this->waitUntil;
    }


    /**
     * check if this ticket is freshly injected in the workflow
     */
    public function isNew()
    {
        return ! empty($this->currentStep);
    }

    /**
     * prepare member data for serializing
     */
    public function toArray()
    {
         $data = array(
             'importItem' => $this->getImportItem()->getIdentifer(),
             'workflow' => $this->getWorkflow()->getIdentifier(),
             'currentStep' => $this->getWorkflow()->getCurrentStep(),
             'blocked'	=> $this->getBlocked(),
             'waitUntil' => $this->waitUntil instanceof DateTime ? $this->waitUntil->format(DATE_ISO8601) : NULL
         );
         return $data;
    }

    /*
     * implenent Serializable
     */

    /**
     * @see Serializable::serialize()
     * @return string
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     *
     * @see Serializable::unserialize()
     * @throws WorkflowException
     * @param string $serialized
     * @return array
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        if (! is_array($data))
        {
            throw new WorkflowException('General Gaddafi while unserializing', WorkflowException::ERROR_UNSERIALIZE);
        }
        $supervisor = Workflow_SupervisorModel::getInstance();
        $this->setImportItem($supervisor->getImportItem($data['importItem']));
        $this->setBlocked($data['blocked'] ? TRUE : FALSE);
        $this->setWaitUntilFromIso8601($data['waitUntil']);

        $workflow = $supervisor->getWorkflowByName($data['workflow']);
        $this->setWorkflow($workflow);
        $this->setCurrentStep($data['currentStep']);

        return $data;
    }
}

?>