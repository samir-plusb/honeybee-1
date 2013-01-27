<?php

namespace Honeybee\Core\Workflow;

use Honeybee\Agavi\User\ZendAclSecurityUser;
use WorkflowInteractivePlugin;
use WorkflowPluginResult;
use IWorkflowPlugin;

/**
 * Representation of one workflow.
 *
 * @author tay
 */
class Process
{
    /**
     * maximal number of executions of one specific workflow step to avoid endless workflow loops
     */
    const MAX_STEP_EXECUTIONS = 2000;

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

    private $resource;

    /**
     * @var Manager
     */
    protected $manager;

    protected $container;

    /**
     * initialize workflow
     *
     * This method is called by {@see Manager
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
     * @throws Exception on invalid workflow structure
     * @see Manager::getWorkflowByName()
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

    public function setWorkflowManager(Manager $manager)
    {
        $this->manager = $manager;
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
     * @throws Exception
     * @param WorkflowTicket $ticket
     */
    public function execute(IResource $resource, $initalGate = NULL, $container = NULL)
    {
        $this->setResource($resource);
        $ticket = $this->getTicket();
        $ticket->setBlocked(TRUE);

        if ($container)
        {
            $this->container = $container;
        }

        $code = self::STATE_NEXT_STEP;
        $firstExec = TRUE;
        while (self::STATE_NEXT_STEP === $code)
        {
            $stepName = $this->getCurrentStep();

            if (! $initalGate)
            {
                $result = $this->executePluginFor($stepName, $firstExec);
                $code = $this->processPluginResultFor($stepName, $result);
            }
            else
            {
                $code = $this->useGate($stepName, $initalGate);
                $initalGate = NULL;
            }

            $firstExec = FALSE;
        }

        return array('code' => $code, 'result' => $result);
    }

    /**
     * execute the plugin for current workflow step
     *
     * @return WorkflowPluginResult
     */
    protected function executePluginFor($stepName, $firstExec = TRUE)
    {
        $result = NULL;
        $plugin = $this->getPluginFor($stepName);
        $shallExecute = FALSE;

        if ($plugin->isInteractive())
        {
            if ($this->hasUserSession() && TRUE === $firstExec)
            {
                $shallExecute = TRUE;
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
            $shallExecute = TRUE;
        }

        if (TRUE === $shallExecute)
        {
            if ($this->getTicket()->incrementStepCount() > self::MAX_STEP_EXECUTIONS)
            {
                throw new Exception(
                    sprintf('To many workflow executions for "%s/%s"', $this->getName(), $stepName),
                    Exception::MAX_STEP_EXECUTIONS_EXCEEDED
                );
            }

            $result = $plugin->process();
        }

        return $result;
    }

    /**
     * prepare next workflow action by evaluating the plugin result
     *
     * @return integer workflow state code
     *
     * @throws Exception
     */
    protected function processPluginResultFor($stepName, WorkflowPluginResult $result)
    {
        $code = NULL;

        switch ($result->getState())
        {
            case WorkflowPluginResult::STATE_OK:
            {
                if (($gate = $result->getGate()))
                {
                    $code = $this->useGate($stepName, $gate);
                }
                else
                {
                    $code = self::STATE_WAITING;
                }
                break;
            }

            case WorkflowPluginResult::STATE_EXPECT_INPUT:
            {
                $code = self::STATE_WAITING;
                break;
            }

            case WorkflowPluginResult::STATE_WAIT_UNTIL:
            {
                $this->getTicket()->setBlocked(FALSE);
                $code = self::STATE_WAITING;
                break;
            }

            default:
            {
                $code = self::STATE_ERROR;
            }
        }

        return $code;
    }

    protected function useGate($stepName, $gateName)
    {
        $plugin = $this->getPluginFor($stepName);
        $gateDef = $this->getGateByName($stepName, $gateName);
        $ticket = $this->getTicket();
        $returnCode = NULL;

        if (NULL === $gateDef)
        {
            throw new Exception("The given workflow gate '$gate' does not exist.");
        }

        try
        {
            $plugin->onResourceLeaving($gateName);
        }
        catch(\Exception $e)
        {
            // atm, can't decide on whether to abort the plugin transistion if the hook fails or not.
            // for now we'll abort as this is a critical issue in most cases probally.
            throw $e;
        }

        switch ($gateDef['type'])
        {
            case 'step':
            {
                $ticket->setWorkflowStep($gateDef['target']);
                try
                {
                    $nextPlugin = $this->getPluginFor($gateDef['target']);
                    $nextPlugin->onResourceEntered($stepName);
                }
                catch(\Exception $e)
                {
                    // atm, can't decide on whether to abort the plugin transistion if the hook fails or not.
                    // for now we'll abort as this is a critical issue in most cases probally.
                    throw $e;
                }

                $returnCode = self::STATE_NEXT_STEP;
                break;
            }
            case 'workflow':
            {
                $ticket->reset();
                $ticket->setWorkflow($gateDef['target']);

                $returnCode = self::STATE_NEXT_WORKFLOW;
                break;
            }
            case 'end':
            {
                $ticket->reset();
                $ticket->setBlocked(FALSE);

                $returnCode = self::STATE_END;
                break;
            }
            default:
            {
                throw new Exception(
                    "The given workflow plugin gate-type '" . $gateDef['type'] . "' is not supported."
                );
            }
        }

        return $returnCode;
    }

    public function hasUserSession()
    {
        $user = \AgaviContext::getInstance()->getUser();
        // we only consider the current execution of being a 'user session',
        // if we have both a valid execution container and a session user.
        return $this->container && $user;
    }

    public function getSessionUser()
    {
        if ($this->hasUserSession())
        {
            $user = \AgaviContext::getInstance()->getUser();

            if (! ($user instanceof ZendAclSecurityUser))
            {
                throw new \InvalidArgumentException(
                    sprintf("User must be instanceof ZendAclSecurityUser, given '%s'", get_class($user))
                );
            }

            return $user;
        }

        return NULL;
    }

    // ---------------------------------- </MAIN WORKFLOW ALGO> ----------------------------------

    protected function getGateByName($stepName, $gateName)
    {
        $pluginGates = $this->steps[$stepName]['plugin']['gates'];

        if (! isset($pluginGates[$gateName]))
        {
            return NULL;
        }

        return $pluginGates[$gateName];
    }

    protected function setResource(IResource $resource)
    {
        $this->resource = $resource;
        $ticket = $this->resource->getWorkflowTicket();

        if ($ticket->isReset())
        {
            $ticket->setWorkflowName($this->getName());
            $ticket->setWorkflowStep($this->firstStep);
        }
    }

    /**
     * Retrieves the currentStep attribute.
     *
     * @return       string the value for currentStep
     */
    protected function getCurrentStep()
    {
        $step = $this->getTicket()->getWorkflowStep();

        if (! $step)
        {
            $step = $this->firstStep;
            $this->getTicket()->setWorkflowStep($step);
        }

        if (! array_key_exists($step, $this->steps))
        {
            // The ticket's current step is not present inside the workflow.
            // This can happen when:
            // 1. The workflow was altered and the step does not exist any more.
            // 2. The ticket is coming from another workflow and was not reset before,
            // thereby still containing a step-name from it's previous surrounding workflow.
            // 3. The ticket was modifed from outside the workflow. In this case find and kill the person who did this.
            throw new Exception(
                'Workflow step does not exists: ' . $step, 
                Exception::STEP_MISSING
            );
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

    public function getFirstStep()
    {
        return $this->firstStep;
    }

    public function getTicket()
    {
        return $this->resource->getWorkflowTicket();
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * find plugin for the current workflow step
     *
     * @param string $step id of workflow step
     *
     * @return IWorkflowPlugin
     *
     * @throws Exception
     */
    public function getPluginFor($stepName)
    {
        /**
         * @todo Get the plugin of the current step. Why is there no step object?
         */
        if (! isset($this->steps[$stepName]['plugin']))
        {
            throw new Exception(
                'Workflow step does not define plugin: ' . $stepName,
                Exception::STEP_MISSING);
        }

        $step = $this->steps[$stepName];

        $pluginName = $step['plugin']['type'];
        $plugin = $this->getPluginByName($pluginName);
        $plugin->initialize($this, $stepName);

        return $plugin;
    }

    /**
     * find and initialize a plugin by its name
     *
     * @param string $pluginName name of plugin
     * @return IWorkflowPlugin
     * @throws Exception on class not found errors or initialize problems
     *
     * @todo If there was a step object then this method would go there.
     */
    public function getPluginByName($pluginName)
    {
        $className = 'Workflow' . ucfirst($pluginName) . 'Plugin';
        if (! class_exists($className, TRUE))
        {
            throw new Exception(
                "Can not find class '$className' for plugin: " . $pluginName,
                Exception::PLUGIN_MISSING);
        }

        $plugin = new $className();
        if (! $plugin instanceof IWorkflowPlugin)
        {
            throw new Exception(
                'Class for plugin is not instance of IWorkflowPlugin: ' . $className,
                Exception::PLUGIN_MISSING);
        }

        return $plugin;
    }

    /**
     * get the labes of defined gates in the current step
     *
     * @return array
     */
    public function getGatesForStep($stepName)
    {
        $gates = $this->steps[$stepName]['plugin']['gates'];
        $ginfo = array();

        foreach ($gates as $gateName => $gateOpts)
        {
            $ginfo[] = $gateName;
        }

        return $ginfo;
    }

    /**
     * get the plugin parameters of current workflow step
     *
     * @return array
     */
    public function getParametersForStep($stepName)
    {
        /**
         * @todo Rename to getPluginParameters nad move to createPlugin or something like that.
         */
        return $this->steps[$stepName]['plugin']['parameters'];
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
