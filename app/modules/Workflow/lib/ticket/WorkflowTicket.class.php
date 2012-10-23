<?php
/**
 * A ticket holds an IWorkflowItem's workflow state, meaning information on what workflow the item is in
 * and at what position in what state the workflow execution has proceeded to.
 * It also keeps track of which steps have been executed how many times and serves as a token,
 * that users must own, when they intend to modify data within the workflow context.
 *
 * @author          tay
 * @version         $Id$
 * @package         Workflow
 * @subpackage      Ticket
 */
class WorkflowTicket extends BaseDocument
{
    /**
     * The name of the null user, that indicates no one is currently owning the ticket.
     *
     * @var string
     */
    const NULL_USER = 'nobody';

    /**
     * The ticket's current revision.
     *
     * @var string
     */
    protected $revision;

    /**
     * Holds the result of the last plugin execution.
     *
     * @var WorkflowPluginResult
     */
    protected $pluginResult;

    /**
     * Holds the name of the workflow that we are currently in.
     *
     * @var string
     */
    protected $workflow = NULL;

    /**
     * Holds the name of our current step.
     *
     * @var string
     */
    protected $currentStep;

    /**
     * Holds id of our workflow item.
     *
     * @var string
     */
    protected $item;

    /**
     * Holds flag that indicates whether we are currently blocked or not.
     *
     * @var boolean
     */
    protected $blocked;

    /**
     * Holds our current session user.
     *
     * @var AgaviUser
     */
    protected $currentOwner;

    /**
     * Holds a future date that we will wait for before proceeding the execution.
     *
     * @var DateTime
     */
    protected $waitUntil;

    /**
     * Holds a timestamp that reflects our last modification date.
     *
     * @var DateTime
     */
    protected $timestamp;

    /**
     * Holds an array that we use to count our step executions.
     * Each executed has a key with an integer that represents the current count.
     *
     * @var array
     */
    protected $stepCounts = array();

    /**
     * An assoc array array holding an arbitary number of key-value pairs.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * If we are run in the context of an AgaviAction,
     * this member will point to the execution container surrounding us.
     *
     * @var AgaviExecutionContainer
     */
    private $container;

    /**
     * Create a fresh workflow ticket instance from the given data and return it.
     *
     * @param array $data
     *
     * @return WorkflowTicket
     */
    public static function fromArray(array $data = array())
    {
        return new WorkflowTicket($data);
    }

    /**
     * Set the ticket's identifier.
     * Either called during hydrate or after a ticket has been stored the first time.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        if (! $this->identifier)
        {
            $this->identifier = $identifier;
        }
    }

    /**
     * Get the ticket's current revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set the ticket's revision.
     * Either called during hydrate or after a ticket has been stored the first time.
     *
     * @param string $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
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
     * Sets the workflow attribute.
     *
     * @param string $workflow name of used workflow
     *
     * @return void
     */
    public function setWorkflow($workflow)
    {
        if ($workflow && !is_string($workflow))
        {
            throw new InvalidArgumentException(
                "Invalid type given to setWorkflow call. Type: " . var_export($workflow, TRUE)
            );
        }
        elseif ($workflow)
        {
            $this->workflow = $workflow;
            $this->onPropertyChanged("workflow");
        }
    }

    /**
     * Return the plugin result from the last ticket processing.
     *
     * @return IWorkflowPluginResult
     */
    public function getPluginResult()
    {
        return $this->pluginResult;
    }

    /**
     * Set the result of the last plugin execution.
     *
     * @see WorkflowHandler::run()
     * @see IWorkflowPlugin::process()
     *
     * @param IWorkflowPluginResult $result
     */
    public function setPluginResult($result)
    {
        if ($result instanceof  IWorkflowPluginResult)
        {
            $this->pluginResult = $result;
            $this->onPropertyChanged("pluginResult");
        }
        else if(is_array($result))
        {
            $this->pluginResult = WorkflowPluginResult::fromArray($result);
            $this->onPropertyChanged("pluginResult");
        }
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
     * Sets the currentStep attribute.
     *
     * @param        string the new value for currentStep
     *
     * @return       void
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;
        $this->onPropertyChanged("currentStep");
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
        $this->onPropertyChanged("stepsCount");
        return $this->stepCounts[$this->currentStep];
    }

    /**
     * Retrieves the workflow item attribute.
     *
     * @return       string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Sets the workflowItem attribute.
     *
     * @param        IWorkflowItem the new value for workflowItem
     *
     * @return       void
     */
    public function setItem($item)
    {
        if ($item instanceof IWorkflowItem)
        {
            $this->item = $item->getIdentifier();
            $this->onPropertyChanged("item");
        }
        else
        {
            $this->item = $item;
            $this->onPropertyChanged("item");
        }
    }

    /**
     * Return the ticket's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Return the value of the parameter with the given name.
     *
     * @param string $name The name of the parameter to get the value for.
     * @param mixed $default The value to return if the parameter is not set.
     *
     * @return mixed Either the parameter value or $default if the parameter is not set.
     */
    public function getParameter($name, $default)
    {
        if (array_key_exists($name, $this->parameters))
        {
            return $this->parameters[$name];
        }
        return $default;
    }

    /**
     * Set a value for the given parameter.
     *
     * @param string $name The name of the value to set the parameter for.
     * @param mixed $value The value to set for the given parameter name.
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
        $this->onPropertyChanged("parameters");
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
     * Sets the blocked attribute.
     *
     * @param        boolean the new value for blocked
     *
     * @return       void
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked ? TRUE : FALSE;
        $this->onPropertyChanged("blocked");
    }

    /**
     * Reset the ticket to start a new workflow.
     */
    public function reset()
    {
        $this->workflow = NULL;
        $this->currentStep = NULL;
        $this->stepCounts = array();
    }

    /**
     * Returns the name of the user currently owning this ticket.
     *
     * @return       string
     */
    public function getCurrentOwner()
    {
        return $this->currentOwner;
    }

    /**
     * Sets the name of the user currently owning this ticket.
     *
     * @param        string
     *
     * @return       void
     */
    public function setCurrentOwner($currentOwner)
    {
        $this->currentOwner = $currentOwner;
        $this->onPropertyChanged("currentOwner");
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
     * Sets the waitUntil attribute.
     *
     * @param        string the new value for waitUntil
     *
     * @return       void
     */
    public function setWaitUntil($waitUntil = NULL)
    {
        if ($waitUntil instanceof DateTime)
        {
            $this->waitUntil = $waitUntil->format(DATE_ISO8601);
            $this->onPropertyChanged("waitUntil");
        }
        else
        {
            $this->waitUntil = $waitUntil;
            $this->onPropertyChanged("waitUntil");
        }
    }

    /**
     * Set the used current excution container while in interactive mode.
     *
     * @param AgaviExecutionContainer $container execution container in interactive mode
     */
    public function setExecutionContainer(AgaviExecutionContainer $container = NULL)
    {
        $this->container = $container;
    }

    /**
     * Gets the execution container in interactive mode.
     *
     * @return AgaviExecutionContainer
     */
    public function getExecutionContainer()
    {
        return $this->container;
    }

    /**
     * Check if we are in interactive mode.
     * @todo only valid with authenticated users?
     *
     * @return boolean
     */
    public function hasUserSession()
    {
        $user = AgaviContext::getInstance()->getUser();
        // we only consider the current execution of being a 'user session',
        // if we have both a valid execution container and a session user.
        return $this->container && $user;
    }

    /**
     * Returns the user for the current (web) session.
     *
     * @return ProjectZendAclSecurityUser
     */
    public function getSessionUser()
    {
        if ($this->hasUserSession())
        {
            $user = AgaviContext::getInstance()->getUser();
            if (! ($user instanceof ProjectZendAclSecurityUser))
            {
                return NULL;
            }
            return $user;
        }
        return NULL;
    }

    /**
     * Check if this ticket is freshly injected in the workflow.
     *
     * @return boolean
     */
    public function isNew()
    {
        return empty($this->currentStep);
    }

    /**
     * Overrides our parent's getPropertyBlacklist method,
     * in order to add our container member to the blacklist of properties
     * that are ignored by toArray and fromArray operations.
     *
     * @return array
     */
    protected function getPropertyBlacklist()
    {
        return array_merge(
            parent::getPropertyBlacklist(),
            array('container')
        );
    }

    protected function hydrate(array $data)
    {
        parent::hydrate($data);

        if (NULL === $this->getCurrentOwner())
        {
            $this->setCurrentOwner(self::NULL_USER);
        }
    }
}

?>
