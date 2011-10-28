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
    protected function createResponseContainer($actionName, $moduleName = NULL,
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
            $arguments = $this->container->getArguments();
        }
        $rsp = $container->createExecutionContainer($moduleName, $actionName, $arguments, $outputType, $requestMethod);
        $rsp->setParameter('is_forward', true);
        return $rsp;
    }
}