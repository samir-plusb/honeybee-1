<?php

/**
 * The WorkflowItem serves as a base implementation of the IWorkflowItem interface.
 * It covers most of the interface only keeping the fromArray method open for implementation.
 *
 * !INFO! The currentState property reflects the workflow-item's ticket's state and is updated each time
 * the item's ticket is stored.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
abstract class WorkflowItem extends BaseDocument implements IWorkflowItem, Zend_Acl_Resource_Interface
{
    /**
     * Holds the WorkflowItem's revision.
     *
     * @var string
     */
    protected $revision;

    /**
     * Holds the WorkflowItem's WorkflowTicket id.
     *
     * @var string
     */
    protected $ticketId;

    /**
     * Holds our import-item.
     *
     * @var IMasterRecord
     */
    protected $masterRecord;

    /**
     * Holds our generic attributes collection.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Holds the current state of the workflow item,
     * meaning, workflow step and owner.
     *
     * @var array
     */
    protected $currentState = array(
        'workflow' => NULL,
        'step'     => NULL,
        'owner'    => NULL
    );

    /**
     * Return the name of the class to use as the IMasterRecord implementation for this class.
     *
     * @return string
     */
    abstract protected function getMasterRecordImplementor();

    /**
     * Return a string to Zend_Acl that will map this object instance
     * to a resource name in zend_acl.
     *
     * @return string
     */
    public function getResourceId()
    {
        return get_class($this);
    }

    /**
     * Returns the IWorkflowItem's current revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Bump the item's revision.
     *
     * @param string $revision
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
        $this->onPropertyChanged("revision");
        return $this;
    }

    /**
     * Return the identifier of our ticket, if we have one.
     *
     * @return string
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    public function setTicketId($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->onPropertyChanged("ticketId");
        return $this;
    }

    /**
     * Set the WorklflowTicket that is responseable for this item.
     *
     * @param WorkflowTicket $ticket
     * @todo remove this method, use getTicketId instead. Looser coupling ftw ...
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function setTicket(WorkflowTicket $ticket)
    {
        $this->ticketId = $ticket->getIdentifier();
        $this->onPropertyChanged("ticketId");
        return $this;
    }

    /**
     * Return the item's current state in the workflow,
     * meaning the workflow step it's in and who owns it at the moment.
     *
     * @return array
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Updates the item's current workflow state (step and owner).
     *
     * @param string $state
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function setCurrentState(array $state)
    {
        $this->currentState = $state;
        $this->onPropertyChanged("currentState");
        return $this;
    }

    /**
     * Return our related master record.
     *
     * @return IMasterRecord
     */
    public function getMasterRecord()
    {
        return $this->masterRecord;
    }

    /**
     * Set an import-item for this workflow-item instance.
     *
     * @param mixed $masterRecord
     *
     * @throws Exception If the workflow-item allready has an import-item or an invalid data-type is passed.
     */
    public function setMasterRecord($masterRecord)
    {
        if ($this->masterRecord)
        {
            throw new Exception("Master record allready exists!");
        }
        if ($masterRecord instanceof IMasterRecord)
        {
            $this->masterRecord = $masterRecord;
        }
        elseif (is_array($masterRecord))
        {
            $this->masterRecord = $this->createMasterRecord($masterRecord);
        }
        else
        {
            throw new Exception("Invalid master record parameter given to setMasterRecord!\n".print_r($masterRecord, TRUE));
        }
        $this->onPropertyChanged("masterRecord");
    }

    /**
     * Create a fresh master-record instance,
     * relating it to our current workflow-item instance.
     *
     * @param array $data
     *
     * @return IMasterRecord
     */
    public function createMasterRecord(array $data)
    {
        $data['parentIdentifier'] = $this->getIdentifier();
        $data['identifier'] = sha1($this->getIdentifier());
        $implementor = $this->getMasterRecordImplementor();
        return $implementor::fromArray($data);
    }

    /**
     * Update the workflow-item's import item with the given values.
     *
     * @param array $masterData
     *
     * @throws Exception If we dont have an import-item.
     */
    public function updateMasterRecord(array $masterData)
    {
        if (! $this->masterRecord)
        {
            throw new Exception("No master-record to update.");
        }
        $this->masterRecord->applyValues($masterData);
    }

    /**
     * Return a generic assoc array of attributes.
     * @todo Implement an AttributeHolder for this?
     *
     * @return array A plain key=>value collection.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the value for the given attribute.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Remove an attribute by name.
     *
     * @param string $name
     */
    public function removeAttribute($name)
    {
        $this->attributes = array();
        foreach ($this->attributes as $key => $value)
        {
            if ($key !== $name)
            {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Return the value for the given attribute name
     * and default if the attribute is not set.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = NULL)
    {
        if (array_key_exists($name, $this->attributes))
        {
            return $this->attributes[$name];
        }
        return $default;
    }
}

?>
