<?php

/**
 * The WorkflowInteractivePlugin serves as the base for interactive plugins.
 * Plugins are considered as interactive when they depend on user input for processing.
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Plugin
 */
class WorkflowInteractivePlugin extends WorkflowBasePlugin
{
    /**
     * Namespace for returning plugin result values from plugin sub execution container
     */
    const NS_PLUGIN_ATTRIBUTES = 'Workflow.Plugin.Result';

    /**
     * Attribute key for accessing the plugin's result object.
     */
    const ATTR_RESULT = 'plugin_result';

    /**
     * Attribute key for returning the plugin result state
     */
    const ATTR_RESULT_STATE = 'state';

    /**
     * Attribute key for returning the plugin result gate
     */
    const ATTR_RESULT_GATE = 'gate';

    /**
     * Attribute key for returning the plugin result message
     */
    const ATTR_RESULT_MESSAGE = 'message';

    /**
     * Return an array that holds data on what action of which module to call
     * when processing this plugin.
     *
     * @return array An array with a key for 'module' and a key for 'action'.
     */
    protected function getPluginAction()
    {
         return array(
            'module' => $this->getParameter('module'),
            'action' => $this->getParameter('action')
        );
    }

    /**
     * Tells whether as plugin is interactive or not.
     * @todo Maybe replace this method by a marker interface IInteractiveWorkflowPlugin and use instanceof checks.
     *
     * @see IWorkflowPlugin::isInteractive()
     *
     * @return boolean
     */
    public final function isInteractive()
    {
        return TRUE;
    }

    /**
     * Execute this plugin, hence run our nested action.
     */
    protected function doProcess()
    {
        $pluginContainer = $this->prepareExecutionContainer();
        $result = new WorkflowInteractivePluginResult();
        $pluginContainer->setAttribute('plugin_result', $result, self::NS_PLUGIN_ATTRIBUTES);
        $result->setResponse($pluginContainer->execute());
        $result->freeze();

        return $result;
    }

    /**
     * Create and initialize an execution container for running our related plugin action.
     *
     * @return AgaviExecutionContainer
     */
    protected function prepareExecutionContainer()
    {
        $actionData = $this->prepareActionData();
        $container = $this->getWorkflow()->getContainer();
        $pluginContainer = $container->createExecutionContainer(
            $actionData['module'],
            $actionData['action'],
            $actionData['arguments'] ? $actionData['arguments'] : $container->getArguments(),
            $actionData['output'],
            $actionData['method']
        );

        if (isset($actionData['parameters']) && is_array($actionData['parameters']))
        {
            foreach ($actionData['parameters'] as $name => $value)
            {
                $pluginContainer->setParameter($name, $value);
            }
        }
        $pluginContainer->setParameter('is_workflow_container', TRUE);

        return $pluginContainer;
    }

    /**
     * Prepare an array with data used to create and initialize the execution container,
     * that is used to run our related workflow action.
     *
     * @return array
     */
    protected function prepareActionData()
    {
        $actionData = $this->getPluginAction();
        return array(
            'module' => $actionData['module'],
            'action' => $actionData['action'],
            'arguments' => isset($actionData['arguments']) ? $actionData['arguments'] : NULL,
            'output' => isset($actionData['output']) ? $actionData['output'] : NULL,
            'method' => isset($actionData['method']) ? $actionData['method'] : NULL
        );
    }

    /**
     * Return the user for the session we are running in.
     *
     * @return AgaviSecurityUser
     */
    protected function getUser()
    {
        return AgaviContext::getInstance()->getUser();
    }
}
