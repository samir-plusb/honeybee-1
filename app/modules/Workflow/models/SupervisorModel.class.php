<?php

class Workflow_SupervisorModel extends ProjectWorkflowBaseModel
{
    /**
     * path relative to app directory to workflow xml definitions
     */
    const WORKFLOW_CONFIG_DIR = 'modules/Workflow/config/workflows/';

    /**
     * @return Workflow_SupervisorModel
     */
    static function getInstance()
    {
        $context = AgaviContext::getInstance();
        $model = $context->getModel('Supervisor', 'Workflow');
        return $model;
    }

    /**
     *
     *
     * @param string $identifier
     * @return IImportItem
     */
    public function getImportItem($identifier)
    {
        return NULL;
    }

    /**
     *
     * @throws AgaviUnreadableException
     * @param string $name
     * @return WorkflowHandler
     */
    public function getWorkflowByName($name)
    {
        $name = strtolower($name);
        if (! preg_match('/^_?[a-z][a-z-0-9]+$/', $name))
        {
            throw new WorkflowException('Workflow name contains invalid characters: '.$name, WorkflowException::INVALID_WORKFLOW_NAME);
        }
        $configPath = self::WORKFLOW_CONFIG_DIR . $name . '.workflow.xml';
        try
        {
            $config = include AgaviConfigCache::checkConfig($configPath);
        }
        catch (AgaviUnreadableException $e)
        {
            throw new WorkflowException($e->getMessage(), WorkflowException::WORKFLOW_NOT_FOUND, $e);
        }
        if (! array_key_exists('workflow', $config))
        {
            throw new WorkflowException('Workflow definition structure is invalid.', WorkflowException::INVALID_WORKFLOW);
        }
        return new WorkflowHandler($config['workflow']);
    }


    /**
     * find and initialize a plugin by its name
     *
     * @param string $pluginName name of plugin
     * @param array $parameters parameters for plugin as defined in the workflow step
     * @return IWorkflowPlugin
     * @throws WorkflowException on class not found errors or initialize problems
     */
    public function getPluginByName($pluginName, array $parameters = NULL)
    {
        $className = 'Workflow'.$pluginName.'Plugin';
        if (! class_exists($className, TRUE))
        {
            throw new WorkflowException("Can not find class '$class' for plugin: ".$pluginName, WorkflowException::PLUGIN_MISSING);
        }

        $plugin = new $className($parameters);
        if (! $plugin instanceof IWorkflowPlugin)
        {
            throw new WorkflowException('Class for plugin is not instance of IWorkflowPlugin: '.$className, WorkflowException::PLUGIN_MISSING);
        }

        return $plugin;
    }
}

?>