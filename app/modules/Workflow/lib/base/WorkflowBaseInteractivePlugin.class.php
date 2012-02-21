<?php

/**
 * This is the simplest plugin which does nothing
 *
 * @author tay
 * @version $Id$
 * @package Workflow
 * @subpackage Plugin
 */
abstract class WorkflowBaseInteractivePlugin extends WorkflowBasePlugin
{
    /**
     *
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
    protected abstract function getPluginAction();

    /**
     * return false to signalize a non interactive plugin by default
     *
     * @see IWorkflowPlugin::isInteractive()
     *
     * @return boolean
     */
    public function isInteractive()
    {
        return TRUE;
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        $user = $this->ticket->getSessionUser();
        if (! $user)
        {
            return FALSE;
        }
        $operation = sprintf(
            '%s::%s',
            $this->getPluginId(),
            $this->ticket->getExecutionContainer()->getRequestMethod()
        );
        return $user->isAllowed(
            $this->ticket->getWorkflowItem(),
            $operation
        );
    }

    /**
     * Execute this plugin, hence run our nested action.
     */
    protected function doProcess()
    {
        $actionData = $this->getPluginAction();
        $moduleName = $actionData['module'];
        $actionName = $actionData['action'];
        $arguments = isset($actionData['arguments']) ? $actionData['arguments'] : NULL;
        $outputType = isset($actionData['output']) ? $actionData['output'] : NULL;
        $requestMethod = isset($actionData['method']) ? $actionData['method'] : NULL;

        $pluginContainer = $this->ticket->createWorkflowExecutionContainer(
            $moduleName,
            $actionName,
            $arguments,
            $outputType,
            $requestMethod
        );

        if (isset($actionData['parameters']) && is_array($actionData['parameters']))
        {
            foreach ($actionData['parameters'] as $name => $value)
            {
                $pluginContainer->setParameter($name, $value);
            }
        }

        $result = new WorkflowInteractivePluginResult();
        $pluginContainer->setAttribute('plugin_result', $result, self::NS_PLUGIN_ATTRIBUTES);
        $result->setResponse($pluginContainer->execute());
        $result->freeze();

        return $result;
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

?>
