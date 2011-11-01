<?php

/**
 * This is the simplest plugin which does nothing
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
abstract class WorkflowBaseInteractivePlugin extends WorkflowBasePlugin
{
    /**
     *
     * Namespace for returning plugin result values from plugin sub execution container
     */
    const NS_RESULT_ATTRIBUTES = 'Workflow.Plugin.Result';

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
     * return false to signalize a non interactive plugin by default
     *
     * @see IWorkflowPlugin::isInteractive()
     *
     * @return boolean
     */
    public function isInteractive()
    {
        return FALSE;
    }

    /**
     * Creates a new container with the same output type and request method as
     * this view's container.
     *
     * This container will have a parameter called 'is_forward' set to true.
     *
     * @param string $actionName The name of the action.
     * @param string $moduleName The name of the module. Defaults to current Module
     * @param mixed $arguments An AgaviRequestDataHolder instance with additional
     *                    request arguments or an array of request parameters.
     * @param string $outputType Optional name of an initial output type to set.
     * @param string $requestMethod Optional name of the request method to be used in this container.
     *
     * @return AgaviExecutionContainer A new execution container instance, fully initialized.
     *
     * @see AgaviView::createForwardContainer
     */
    protected function executePluginAction($actionName, $moduleName = NULL,
        $arguments = NULL, $outputType = NULL, $requestMethod = NULL)
    {
        $container = $this->ticket->getExecutionContainer();
        if (NULL === $moduleName)
        {
            $moduleName = $container->getModuleName();
        }
        if ($arguments !== null)
        {
            if (!($arguments instanceof AgaviRequestDataHolder))
            {
                $rdhc = $this->context->getRequest()->getParameter('request_data_holder_class');
                $arguments = new $rdhc(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => $arguments));
            }
        }
        else
        {
            $arguments = $container->getArguments();
        }

        $pluginContainer = $container->createExecutionContainer($moduleName, $actionName, $arguments, $outputType, $requestMethod);
        $pluginContainer->setParameter('is_forward', true);

        // put the gate labels as parameter to the plugin container
        $pluginContainer->setParameter('gates', $this->getGates());

        $response = $pluginContainer->execute();

        $rdata = $pluginContainer->getAttributes(self::NS_RESULT_ATTRIBUTES);
        return new WorkflowInteractivePluginResult(
            $response,
            $rdata[self::ATTR_RESULT_STATE], $rdata[self::ATTR_RESULT_GATE], $rdata[self::ATTR_RESULT_MESSAGE]);
    }


    /**
     * store the values needed for generating WorkflowPluginResults in the given attribute holder
     *
     * call this method at end of plugin action
     *
     * @param AgaviAttributeHolder $store container to store the attributes (eg. current execution container)
     * @param integer $state plugin result state
     * @param integer $gate plugin result gate
     * @param string $message plugin result message
     */
    public static function setPluginResultAttributes(AgaviAttributeHolder $store,
        $state, $gate = WorkflowPluginResult::GATE_NONE, $message = NULL)
    {
        $result = array(
            self::ATTR_RESULT_STATE => $state,
            self::ATTR_RESULT_GATE => $gate,
            self::ATTR_RESULT_MESSAGE => $message
        );
        $store->setAttributes($result, self::NS_RESULT_ATTRIBUTES);
    }
}