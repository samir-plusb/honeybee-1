<?php
/**
 * A ticket holds the state of one content item in the the associated workflow.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowTicket extends AgaviParameterHolder implements Serializable
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
     * modification time
     *
     * @var DateTime
     */
    protected $timestamp;

    /**
     *
     * @var array
     */
    protected $stepCounts = array();

    /**
     *
     * @var AgaviExecutionContainer
     */
    private $container;

    /**
     * get the persistent id of ticket if available
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * set identifier (primary key)
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->id = $identifier;
    }


    /**
     * get sub revison id
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->rev;
    }


    /**
     * set sub revision id (version) of ticket
     *
     * @param string $revision
     */
    public function setRevision($revision)
    {
        $this->rev = $revision;
    }


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
     * get last plugin result if any
     *
     * @return WorkflowPluginResult
     */
    public function getPluginResult()
    {
        return $this->result;
    }

    /**
     * return the number of executions of the current step
     *
     * @return integer
     */
    public function countStep()
    {
        $this->stepCounts[$this->currentStep] =
            isset($this->stepCounts[$this->currentStep])
                ? $this->stepCounts[$this->currentStep] + 1
                : 1;
        return $this->stepCounts[$this->currentStep];
    }


    /**
     * reset the ticket to start a new workflow
     */
    public function reset()
    {
        $this->workflow = NULL;
        $this->currentStep = NULL;
        $this->stepCounts = array();
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
     * Check the blocked attribute.
     *
     * @return       boolean the value for blocked
     */
    public function isBlocked()
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
        $this->touch();
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
            '_id' => $this->id,
            '_rev' => $this->rev,
            'type' => get_class($this),
            'ts' => $this->timestamp->format(DATE_ISO8601),
            'item' => $this->getImportItem()->getIdentifier(),
            'workflow' => $this->getWorkflow(),
            'step' => $this->getCurrentStep(),
            'blocked' => $this->isBlocked(),
            'wait' => $this->waitUntil instanceof DateTime ? $this->waitUntil->format(DATE_ISO8601) : NULL,
            'result' => $this->result ? $this->result->toArray() : NULL,
            'counts' => $this->stepCounts,
            'p' => $this->getParameters()
        );
        return array_filter($data);
    }


    /**
     * initialize object menber variables from data array
     *
     * @param array $data member variable values from unserilizing or json decode result
     * @return
     */
    public function fromArray(array $data)
    {
        $this->id = empty($data['_id']) ? NULL : $data['_id'];
        $this->rev = empty($data['_rev']) ? NULL : $data['_rev'];
        $this->timestamp = new DateTime(empty($data['ts']) ? NULL : $data['ts']);
        $this->parameters = isset($data['p']) && is_array($data['p']) ? $data['p'] : array();

        if (array_key_exists('item', $data))
        {
            if ($data['item'] instanceof IDataRecord)
            {
                $this->setImportItem($data['item']);
            }
            else
            {
                $itemPeer = Workflow_SupervisorModel::getInstance()->getItemPeer();
                $this->setImportItem($itemPeer->getItemByIdentifier($data['item']));
            }
        }

        $this->setBlocked( isset($data['blocked']) && $data['blocked']);
        $this->setWaitUntilFromIso8601( empty($data['wait']) ? NULL : $data['wait']);
        $this->setWorkflow( empty($data['workflow']) ? NULL : $data['workflow']);
        $this->setCurrentStep( empty($data['step']) ? NULL : $data['step']);

        if (isset($data['result']))
        {
            $this->setPluginResult(WorkflowPluginResult::fromArray($data['result']));
        }
    }

    /**
     * freshen timestamp
     */
    public function touch()
    {
        $this->timestamp = new DateTime();
    }

    /**
     * set the used current excution container while in interactive mode
     *
     * @param AgaviExecutionContainer $container execution container in interactive mode
     */
    public function setExecutionContainer(AgaviExecutionContainer $container = NULL)
    {
        $this->container = $container;
    }


    /**
     * gets the execution container in interactive mode
     *
     * @return AgaviExecutionContainer
     */
    public function getExecutionContainer()
    {
        return $this->container;
    }


    /**
     * check if in interactive mode
     *
     * @return boolean
     */
    public function hasUserSession()
    {
        return NULL != $this->container;
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