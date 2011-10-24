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
     *
     * @var string document id in database
     */
    private $id;

    /**
     *
     * @var string document release in database
     */
    private $rev;

    /**
     *
     * @var WorkflowPluginResult result of last processed plugin
     */
    protected $result;

    /**
     * The ticket belongs to this workflow
     *
     * @var string
     */
    protected $workflow;


    /**
     * @var string identifier of current workflow step
     */
    protected $currentStep;

    /**
     * The administrated content item
     *
     * @var IDataRecord
     */
    protected $importItem;

    /**
     * the tickets lock status
     *
     * @var boolean
     */
    protected $blocked;

    /**
     * @todo fill in documentation here
     *
     * @var AgaviUser
     */
    protected $currentOwner;

    /**
     * @todo fill in documentation here
     *
     * @var DateTime
     */
    protected $waitUntil;


    /**
     * Set the plugin result state
     *
     * This method must only used by plugings to store their result state
     *
     * @see WorkflowHandler::run()
     * @see IWorkflowPlugin::process()
     *
     * @param WorkflowPluginResult $result from plugin process
     *
     * @return void
     */
    public function setPluginResult(WorkflowPluginResult $result)
    {
        $this->result = $result;
    }

    /**
     * gets the plugin result state
     * @return integer
     */
    public function getState()
    {
        return $this->result->getState();
    }

    /**
     * get status message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->result->getMessage();
    }

    /**
     * Sets the workflow attribute.
     *
     * @param string $workflow name of used workflow
     *
     * @return void
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Retrieves the workflow attribute.
     *
     * @return string name of used workflow
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
     * @param        IDataRecord the new value for importItem
     *
     * @return       void
     */
    public function setImportItem(IDataRecord $importItem)
    {
        $this->importItem = $importItem;
    }

    /**
     * Retrieves the importItem attribute.
     *
     * @return       IDataRecord the value for importItem
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
        $this->blocked = $blocked ? TRUE : FALSE;
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
     * set display
     *
     * @param unknown_type $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
     * initialize instance
     *
     * @param array $data from deserializing or database loading
     */
    public function __construct(array $data = NULL, IDataRecord $record = NULL)
    {
        if ($record)
        {
            $data['importItem'] = $record;
        }

        $this->setBlocked(TRUE);
        if (is_array($data))
        {
            $this->fromArray($data);
        }
    }

    /**
     * prepare member data for serializing
     */
    public function toArray()
    {
         $data = array(
             'importItem' => $this->getImportItem()->getIdentifier(),
             'workflow' => $this->getWorkflow(),
             'currentStep' => $this->getCurrentStep(),
             'blocked' => $this->getBlocked(),
             'waitUntil' => $this->waitUntil instanceof DateTime ? $this->waitUntil->format(DATE_ISO8601) : NULL,
             'result' => $this->result ? $this->result->toArray() : NULL
         );
         if (NULL !== $this->id)
         {
             $data['_id'] = $this->id;
         }
         if (NULL !== $this->rev)
         {
             $data['_rev'] = $this->rev;
         }
         return $data;
    }


    /**
     * initialize object menber variables from data array
     *
     * @param array $data member variable values from unserilizing or json decode result
     * @return
     */
    public function fromArray(array $data)
    {
        $supervisor = Workflow_SupervisorModel::getInstance();
        $this->id = empty($data['id']) ? NULL : $data['id'];
        $this->rev = empty($data['rev']) ? NULL : $data['rev'];

        if (array_key_exists('importItem', $data))
        {
            if ($data['importItem'] instanceof IDataRecord)
            {
                $this->setImportItem($data['importItem']);
            }
            else
            {
                $this->setImportItem($supervisor->getImportItem($data['importItem']));
            }
        }

        $this->setBlocked($data['blocked'] ? TRUE : FALSE);
        $this->setWaitUntilFromIso8601($data['waitUntil']);
        $this->setWorkflow($data['workflow']);
        $this->setCurrentStep($data['currentStep']);

        if (isset($data['result']))
        {
            $this->setPluginResult(WorkflowPluginResult::fromArray($data['result']));
        }
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
        $this->fromArray($data);
        return $data;
    }


    /**
     * Ticket as printable string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s(Item "%s", Workflow %s/%s, %s)',
            get_class($this),
            ($this->importItem ? $this->importItem->getIdentifier() : ''),
            $this->workflow, $this->currentStep, $this->result);
    }
}

?>