<?php

/**
 * The ProjectBaseAction serves as the base action to all actions implemented inside of this project.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Agavi
 * @subpackage      Action
 */
class ProjectBaseAction extends AgaviAction
{
    /**
     * Default error handling for all methods
     *
     * @param AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $container = $this->getContainer();
        $validation_manager = $container->getValidationManager();
        $this->setAttribute('_module', $container->getModuleName());
        $this->setAttribute('_action', $container->getActionName());
        $this->setAttribute('errors', $validation_manager->getReport()->getErrors());

        if (! $this->hasAttribute("method"))
        {
            $this->setAttribute("method", __METHOD__);
        }

        $this->getContext()->getTranslationManager()->setDefaultDomain($container->getModuleName().'.errors');

        return array(
            AgaviConfig::get('actions.error_404_module', 'Default'),
            AgaviConfig::get('actions.error_404_action', 'Error404')
                .'/'.AgaviConfig::get('actions.error_404_action', 'Error404').'Success');
    }


    /**
     * Default error handling for method Read (GET Requests)
     *
     * @param AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     */
    public function handleReadError(AgaviRequestDataHolder $parameters)
    {
        $this->setAttribute("method", __METHOD__);

        return $this->handleError($parameters);
    }

    /**
     * Default error handling for method Write (POST Requests)
     *
     * @param AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters)
    {
        $this->setAttribute("method", __METHOD__);

        return $this->handleError($parameters);
    }

    public function isSecure()
    {
        return TRUE;
    }

    protected function logError($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('error');
        $errMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($errMsg, AgaviLogger::ERROR)
        );
    }

    protected function logInfo($msg)
    {
        $logger = $this->getContext()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($infoMsg, AgaviLogger::INFO)
        );
    }

    protected function getModule()
    {
        $module = $this->getContext()->getRequest()->getAttribute('module', 'org.honeybee.env');

        if (! ($module instanceof HoneybeeModule))
        {
            throw new Exception(
                "Unable to determine honebee-module for the current action's scope." . PHP_EOL . 
                "Make sure that the HoneybeeModuleRoutingCallback is executed for the related route."
            );
        }

        return $module;
    }

    /**
     * add a validation error out of the action
     *
     * @param string $argument argument name
     * @param string $message error message
     * @param int $severity
     * @return AgaviValidationIncident the generated error
     */
    protected function addError($argument, $message, $severity = AgaviValidator::ERROR)
    {
        $validation_manager = $this->getContainer()->getValidationManager();
        $incident = new AgaviValidationIncident(NULL, $severity);
        $incident->addError(new AgaviValidationError($message, NULL, array($argument)));
        $validation_manager->addIncident($incident);

        return $incident;
    }
}
