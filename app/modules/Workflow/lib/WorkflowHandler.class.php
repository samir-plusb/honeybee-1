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
        $this->setTicket($ticket);

        if (! array_key_exists($this->getTicket()->getCurrentStep(), $this->steps))
        {
            throw new WorkflowException('Workflow step does not exists: '.$ticket->getCurrentStep(), WorkflowException::INVALID_STEP);
        }

        $plugin = $this->getPluginForCurrentStep();
        if (! $plugin->isInteractive())
        {
            $result = $plugin->process();
        }
        else if (Workflow_SupervisorModel::getInstance()->isInteractive())
        {
            $result = $plugin->process();
        }


        return $result;
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
        return $this->getTicket()->getCurrentStep();
    }

    /**
     * Retrieves the ticket attribute.
     *
     * @return       Workflow_TicketModel the value for ticket
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
     *
     * @return IWorkflowPlugin
     * @throws WorkflowException
     */
    protected function getPluginForCurrentStep()
    {
        if (! isset($this->steps[$ticket->getCurrentStep()]['plugin']))
        {
            throw new WorkflowException('Workflow step does not define plugin: '.$ticket->getCurrentStep(), WorkflowException::STEP_MISSING);
        }
        $pluginName = $this->steps[$ticket->getCurrentStep()]['plugin'];
        $plugin = Workflow_SupervisorModel::getInstance()->getPluginByName($pluginName);
        $plugin->initialize($this->getTicket(), $this->getStepParameters());

        return $plugin;
    }
}

?>