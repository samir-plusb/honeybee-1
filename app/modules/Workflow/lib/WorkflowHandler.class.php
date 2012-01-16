<?php
/**
 * Representation of one workflow.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowHandler
{
    /**
     * maximal number of executions of one specific workflow step to avoid endless workflow loops
     */
    const MAX_STEP_EXECUTIONS = 20;

    /**
     * workflow process error
     */
    const STATE_ERROR = 0;

    /**
     * Workflow has changed; start the new one
     */
    const STATE_NEXT_WORKFLOW = 1;

    /**
     * Workflow has ended successfully
     */
    const STATE_END = 2;

    /**
     * process the next workflow step
     */
    const STATE_NEXT_STEP = 3;

    /**
     * interupt workflow on current step
     */
    const STATE_WAITING = 4;

    /**
     * Name of workflow
     *
     * @var string
     */
    protected $name;

    /**
     * Long workflow description
     *
     * @var string
     */
    protected $description;

    /**
     * id/key of first workflow step
     *
     * @var string
     */
    protected $firstStep;

    /**
     * definition of workflow steps
     *
     * @var array
     */
    protected $steps;

    /**
     * Workflow state object
     *
     * @var WorkflowTicket
     */
    private $ticket;

    /**
     * initialize workflow
     *
     * This method is called by {@see Workflow_SupervisorModel::getWorkflowByName()} after reading and parsing a
     * workflow xml definition.
     *
     * The array for $config parameter has the form:
     *
     * <pre>
     *  array (
     *      'workflow' => array (
     *          'name' => 'news',
     *          'description' => {WORKFLOW DESCRIPTION},
     *          'start' => {NAME OF THE STEP TO START THE WORKFLOW WITH},
     *          'steps' => array (
     *              {STEP NAME} => array (
     *                  'description' => {WORKFLOW DESCRIPTION},
     *                  'plugin' => array (
     *                      'type' => {WORKFLOW PLUGIN ALIAS},
     *                      'gates' => array (
     *                          {GATE NAME} => {VALID WORKFLOW STEP NAME},
     *                          ...
     *                      ),
     *                      'parameters' => array (
     *                          {PARAMETER NAME} => {PARAMETER VALUE},
     *                          ...
     *                      ),
     *                  )
     *              ),
     *              ...
     *          )
     *      )
     *  )
     *  </pre>
     *
     * @throws WorkflowException on invalid workflow structure
     * @see Workflow_SupervisorModel::getWorkflowByName()
     * @param array $config parse result of xml workflow definition in the format
     *                      AgaviReturnArrayConfigHandler::convertToArray
     */
    public function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->description = $config['description'];
        $this->firstStep = $config['start_at'];
        $this->steps = $config['steps'];
    }

    /**
     * Retrieves the name attribute.
     *
     * @return       mixed the value for name
     */
    public function getName()
    {
        return $this->name;
    }

    // ---------------------------------- <MAIN WORKFLOW ALGO> -----------------------------------

    /**
     * pull the ticket through the workflow
     *
     * @throws WorkflowException
     * @param WorkflowTicket $ticket
     */
    public function run(WorkflowTicket $ticket, $initalGate = NULL)
    {
        $this->setTicket($ticket);
        $this->ticket->setBlocked(TRUE);

        $code = self::STATE_NEXT_STEP;
        while (self::STATE_NEXT_STEP === $code)
        {
            $currentStep = $this->getCurrentStep();
            if ($this->ticket->countStep() > self::MAX_STEP_EXECUTIONS)
            {
                throw new WorkflowException(
                    sprintf('To many workflow executions for "%s/%s"', $this->getName(), $currentStep),
                    WorkflowException::MAX_STEP_EXECUTIONS_EXCEEDED
                );
            }

            if (NULL !== $initalGate)
            {
                $code = $this->useGate($initalGate);
                $initalGate = NULL;
            }
            else
            {
                $result = $this->executePlugin();
                $ticket->setPluginResult($result);
                $code = $this->processPluginResult($result);
            }

            $this->getPeer()->saveTicket($this->getTicket());
        }
        // @todo Unset ticket afterwards?
        // Not that it would matter as this instance is thrown away after run...
        return $code;
    }

    /**
     * execute the plugin for current workflow step
     *
     * @return WorkflowPluginResult
     */
    protected function executePlugin()
    {
        $result = NULL;
        $plugin = $this->getPluginFor($this->getCurrentStep());

        if ($plugin->isInteractive())
        {
            if ($this->getTicket()->hasUserSession())
            {
                $result = $plugin->process();
            }
            else
            {
                $this->getTicket()->setBlocked(FALSE);
                $result = new WorkflowPluginResult();
                $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
                $result->setMessage("waiting for input ...");
            }
        }
        else
        {
            $result = $plugin->process();
        }
        return $result;
    }

    /**
     * prepare next workflow action by evaluating the plugin result
     *
     * @return integer workflow state code
     *
     * @throws WorkflowException
     */
    protected function processPluginResult(WorkflowPluginResult $result)
    {
        $ticket = $this->getTicket();
        switch ($result->getState())
        {
            case WorkflowPluginResult::STATE_OK:
            {
                if (($gate = $result->getGate()))
                {
                    return $this->useGate($gate);
                }
                return self::STATE_WAITING;
            }
            case WorkflowPluginResult::STATE_EXPECT_INPUT:
                return self::STATE_WAITING;
            case WorkflowPluginResult::STATE_WAIT_UNTIL:
                $ticket->setBlocked(FALSE);
                return self::STATE_WAITING;
            default:
                return self::STATE_ERROR;
        }
    }

    protected function useGate($gate)
    {
        $gateDef = $this->getGateByName($gate);

        if (NULL === $gateDef)
        {
            throw new WorkflowException(
                "The given workflow gate '" . $gate . "' does not exist."
            );
        }

        switch ($gateDef['type'])
        {
            case 'step':
                $this->setCurrentStep($gateDef['target']);
                return self::STATE_NEXT_STEP;
            case 'workflow':
                $ticket->reset();
                $ticket->setWorkflow($gateDef['target']);
                return self::STATE_NEXT_WORKFLOW;
            case 'end':
                $this->ticket->reset();
                $this->ticket->setBlocked(FALSE);
                return self::STATE_END;
            default:
                throw new WorkflowException(
                    "The given workflow plugin gate-type '" . $gateDef['type'] . "' is not supported."
                );
        }
    }

    // ---------------------------------- </MAIN WORKFLOW ALGO> ----------------------------------

    /**
     *
     *
     * @param WorkflowPluginResult $result
     */
    protected function getGateByName($gate)
    {
        $pluginGates = $this->steps[$this->getCurrentStep()]['plugin']['gates'];
        if (! isset($pluginGates[$gate]))
        {
            return NULL;
        }
        return $pluginGates[$gate];
    }

    /**
     * assoziate the ticket to the workflow
     *
     * @param WorkflowTicket $ticket
     */
    protected function setTicket(WorkflowTicket $ticket)
    {
        if ($ticket->isNew())
        {
            $ticket->setWorkflow($this->getName());
            $ticket->setCurrentStep($this->firstStep);
        }
        $this->ticket = $ticket;
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
        $this->getTicket()->setCurrentStep($currentStep);
    }

    /**
     * Retrieves the currentStep attribute.
     *
     * @return       string the value for currentStep
     */
    public function getCurrentStep()
    {
        $step = $this->getTicket()->getCurrentStep();
        if (! $step)
        {
            $step = $this->firstStep;
            $this->setCurrentStep($step);
        }

        if (! array_key_exists($step, $this->steps))
        {
            // The ticket's current step is not present inside the workflow.
            // This can happen when:
            // 1. The ticket was modifed from outside the workflow. In this case find and kill the person who did this.
            // 2. The ticket is coming from another workflow and was not reset before,
            // thereby still containing a step-name from it's previous surrounding workflow.
            throw new WorkflowException('Workflow step does not exists: '.$step, WorkflowException::STEP_MISSING);
        }

        return $step;
    }

    public function getStep($name)
    {
        if (isset($this->steps[$name]))
        {
            return $this->steps[$name];
        }
        return NULL;
    }

    /**
     * Retrieves the ticket attribute.
     *
     * @return       WorkflowTicket the value for ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * get the plugin parameters of current workflow step
     *
     * @return array
     */
    protected function getStepParameters()
    {
        /**
         * @todo Rename to getPluginParameters nad move to createPlugin or something like that.
         */
        return $this->steps[$this->getCurrentStep()]['plugin']['parameters'];
    }

    /**
     * get the labes of defined gates in the current step
     *
     * @return array
     */
    protected function getCurrentGates()
    {
        /**
         * @todo Get the gates of the current step. Why is there no step object?
         */
        $gates = $this->steps[$this->getCurrentStep()]['plugin']['gates'];
        $ginfo = array();
        foreach ($gates as $idx => $gate)
        {
            $ginfo[] = empty($gate['value']) ? 'Gate '.$idx : $gate['value'];
        }
        return $ginfo;
    }

    /**
     * find plugin for the current workflow step
     *
     * @param string $step id of workflow step
     *
     * @return IWorkflowPlugin
     *
     * @throws WorkflowException
     */
    public function getPluginFor($step)
    {
        /**
         * @todo Get the plugin of the current step. Why is there no step object?
         */
        if (! isset($this->steps[$step]['plugin']))
        {
            throw new WorkflowException(
                'Workflow step does not define plugin: '.$step,
                WorkflowException::STEP_MISSING);
        }
        $step = $this->steps[$step];

        $pluginName = $step['plugin']['type'];
        $plugin = $this->getPluginByName($pluginName);
        $plugin->initialize($this->getTicket(), $this->getStepParameters(), $this->getCurrentGates());

        return $plugin;
    }

    /**
     * find and initialize a plugin by its name
     *
     * @param string $pluginName name of plugin
     * @return IWorkflowPlugin
     * @throws WorkflowException on class not found errors or initialize problems
     *
     * @todo If there was a step object then this method would go there.
     */
    public function getPluginByName($pluginName)
    {
        $className = 'Workflow'.ucfirst($pluginName).'Plugin';
        if (! class_exists($className, TRUE))
        {
            throw new WorkflowException(
                "Can not find class '$className' for plugin: ".$pluginName,
                WorkflowException::PLUGIN_MISSING);
        }

        $plugin = new $className();
        if (! $plugin instanceof IWorkflowPlugin)
        {
            throw new WorkflowException(
                'Class for plugin is not instance of IWorkflowPlugin: '.$className,
                WorkflowException::PLUGIN_MISSING);
        }

        return $plugin;
    }

    /**
     * @todo Rename to getTicketPeer
     * @return WorkflowTicketPeer
     */
    public function getPeer()
    {
        return Workflow_SupervisorModel::getInstance()->getTicketPeer();
    }

    /**
     * return instance info as string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s(name "%s"; first %s; steps=%s)',
            get_class($this), $this->name, $this->firstStep, implode(', ', array_keys($this->steps)));
    }
}

?>
