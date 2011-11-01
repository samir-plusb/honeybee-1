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
     * array(
     *     [name] => …
     *     [description] => …
     *     [steps] => Array
     *     (
     *         [start] => {ID OF FIRST WORKFLOW STEP}
     *         [ID STEP 1] => Array
     *         (
     *              [name] => {WORKFLOW STEP NAME}
     *              [plugin] => {PLUGIN NAME}
     *              [gates] => Array
     *              (
     *                  [0] => Array
     *                  (
     *                      // one of the following:
     *                      [workflow] => {NAME OF NEXT WORKFLOW}
     *                      [ref] => {ID OF NEXT STEP}
     *                      [end] => {WORKFLOW END}
     *                      [value] => {GATE DESCRIPTION}
     *                  )
     *                  [1] => Arrray(…)
     *                  …
     *               )
     *          )
     *          [ID STEP 2] => Array(…)
     *          …
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
        $this->description = empty($config['description']) ? '' : $config['description'];

        if (empty($config['steps']['start']))
        {
            $steps = array_keys($config['steps']);
            $this->firstStep = $steps[0];
        }
        else
        {
            /* @todo check for workflow steps named 'start' */
            $this->firstStep = $config['steps']['start'];
            unset($config['steps']['start']);
        }
        $this->steps = $config['steps'];
        if (empty($this->steps))
        {
            throw new WorkflowException('Workflow is empty', WorkflowException::INVALID_WORKFLOW);
        }
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


    /**
     * pull the ticket through the workflow
     *
     * @throws WorkflowException
     * @param WorkflowTicket $ticket
     */
    public function run(WorkflowTicket $ticket)
    {
        /* @todo Remove debug code WorkflowHandler.class.php from 24.10.2011 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log($this,AgaviILogger::DEBUG);

        $this->setTicket($ticket);

        $code = self::STATE_NEXT_STEP;
        while (self::STATE_NEXT_STEP === $code)
        {
            $currentStep = $this->getCurrentStep();
            /* @todo Remove debug code WorkflowHandler.class.php from 27.10.2011 */
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
            $__logger->log(sprintf('start "%s" step: %s', $this->name, $currentStep),AgaviILogger::DEBUG);

            if ($ticket->countStep() > self::MAX_STEP_EXECUTIONS)
            {
                throw new WorkflowException(
                    sprintf('To many workflow executions for "%s/%s"', $this->getName(), $currentStep),
                    WorkflowException::MAX_STEP_EXECUTIONS_EXCEEDED);
            }

            $result = $this->executePlugin();
            /* @todo Remove debug code WorkflowHandler.class.php from 31.10.2011 */
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
            $__logger->log($result,AgaviILogger::DEBUG);

            $ticket->setPluginResult($result);
            $code = $this->prepareNextAction($result);

            $this->getPeer()->saveTicket($this->getTicket());
        }

        return $code;
    }

    /**
     * prepare next workflow action by evaluating the plugin result
     *
     * @return integer workflow state code
     *
     * @throws WorkflowException
     */
    protected function prepareNextAction(WorkflowPluginResult $result)
    {
        $ticket = $this->getTicket();
        switch ($result->getState())
        {
            case WorkflowPluginResult::STATE_OK:
                $gate = $this->getGate($result);
                if (! empty($gate['workflow']))
                {
                    $ticket->reset();
                    $ticket->setWorkflow($gate['workflow']);
                    return self::STATE_NEXT_WORKFLOW;
                }
                else if (! empty($gate['ref']))
                {
                    $this->setCurrentStep($gate['ref']);
                    return self::STATE_NEXT_STEP;
                }
                else if (array_key_exists('end', $gate) && $gate['end'])
                {
                    $ticket->reset();
                    $ticket->setBlocked(FALSE);
                    return self::STATE_END;
                }
                else
                {
                    throw new WorkflowException('Gate has no action', WorkflowException::GATE_WITHOUT_ACTION);
                }
                break;

            case WorkflowPluginResult::STATE_EXPECT_INPUT:
                return self::STATE_WAITING;

            case WorkflowPluginResult::STATE_WAIT_UNTIL:
                $ticket->setBlocked(FALSE);
                return self::STATE_WAITING;

            default:
                return self::STATE_ERROR;
        }
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
            if ($this->getTicket()->isInteractive() && $this->isAuthenticated())
            {
                $result = $plugin->process();
            }
            else
            {
                $this->getTicket()->setBlocked(FALSE);
                $result = new WorkflowPluginResult(WorkflowPluginResult::STATE_EXPECT_INPUT);
            }
        }
        else
        {
            $result = $plugin->process();
        }
        return $result;
    }

    /**
     *
     *
     * @param WorkflowPluginResult $result
     */
    protected function getGate(WorkflowPluginResult $result)
    {
        return $this->steps[$this->getCurrentStep()]['gates'][$result->getGate()];
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
            $ticket->setWorkflow($this);
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
            throw new WorkflowException('Workflow step does not exists: '.$step, WorkflowException::STEP_MISSING);
        }

        return $step;
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
        if (array_key_exists('parameters', $this->steps[$this->getCurrentStep()]))
        {
            return $this->steps[$this->getCurrentStep()]['parameters'];
        }
        return array();
    }


    /**
     * get the labes of defined gates in the current step
     *
     * @return array
     */
    protected function getCurrentGates()
    {
        $gates = $this->steps[$this->getCurrentStep()]['gates'];
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
     * @param string $currentStep id of workflow step
     *
     * @return IWorkflowPlugin
     *
     * @throws WorkflowException
     */
    protected function getPluginFor($currentStep)
    {
        if (! isset($this->steps[$currentStep]['plugin']))
        {
            throw new WorkflowException(
                'Workflow step does not define plugin: '.$currentStep,
                WorkflowException::STEP_MISSING);
        }
        $step = $this->steps[$currentStep];

        $pluginName = $step['plugin'];
        $plugin = Workflow_SupervisorModel::getInstance()->getPluginByName($pluginName);
        $plugin->initialize($this->getTicket(), $this->getStepParameters(), $this->getCurrentGates());

        return $plugin;
    }


    /**
     *
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