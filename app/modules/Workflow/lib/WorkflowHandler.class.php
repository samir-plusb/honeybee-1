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
    protected $ticket;




    /**
     * initialize workflow
     *
     * This method is called by {@see Workflow_SupervisorModel::getWorkflowByName()} after reading and parsing a
     * workflow xml definition.
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
            $code = $this->main();

            /* @todo Remove debug code WorkflowHandler.class.php from 24.10.2011 */
            $__logger=AgaviContext::getInstance()->getLoggerManager();
            $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
            $__logger->log('Step return code: '.$code,AgaviILogger::DEBUG);
        }

        return $code;
    }

    /**
     *
     * @return integer workflow state code
     */
    protected function main()
    {
        /* @todo Remove debug code WorkflowHandler.class.php from 24.10.2011 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log('process step: '.$this->getCurrentStep(),AgaviILogger::DEBUG);

        $plugin = $this->getPluginForCurrentStep();
        if (! $plugin->isInteractive())
        {
            $result = $plugin->process();
        }
        else if (Workflow_SupervisorModel::getInstance()->isInteractive())
        {
            $result = $plugin->process();
        }

        $this->getTicket()->setPluginResult($result);

        if ($result->canProceedToNextStep())
        {
            $gate = $this->getGate($result);
            if (! empty($gate['workflow']))
            {
                $this->getTicket()->setWorkflow($gate['workflow']);
                return self::STATE_NEXT_WORKFLOW;
            }
            else if (! empty($gate['value']))
            {
                $this->setCurrentStep($currentStep);
                return self::STATE_NEXT_STEP;
            }
            else
            {
                return self::STATE_END;
            }
        }

        /* @todo Remove debug code WorkflowHandler.class.php from 21.10.2011 */
        $__logger=AgaviContext::getInstance()->getLoggerManager();
        $__logger->log(__METHOD__.":".__LINE__." : ".__FILE__,AgaviILogger::DEBUG);
        $__logger->log($this->getTicket(),AgaviILogger::DEBUG);
        $__logger->log($result,AgaviILogger::DEBUG);


        return self::STATE_ERROR;
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
            throw new WorkflowException(
                'Workflow step does not exists: '.$ticket->getCurrentStep(),
                WorkflowException::STEP_MISSING);
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
     * find plugin for the current workflow step
     *
     * @return IWorkflowPlugin
     * @throws WorkflowException
     */
    protected function getPluginForCurrentStep()
    {
        $currentStep = $this->getCurrentStep();
        if (! isset($this->steps[$currentStep]['plugin']))
        {
            throw new WorkflowException(
                'Workflow step does not define plugin: '.$currentStep,
                WorkflowException::STEP_MISSING);
        }
        $pluginName = $this->steps[$currentStep]['plugin'];
        $plugin = Workflow_SupervisorModel::getInstance()->getPluginByName($pluginName);
        $plugin->initialize($this->getTicket(), $this->getStepParameters());

        return $plugin;
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