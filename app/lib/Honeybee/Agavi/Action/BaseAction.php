<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Agavi\Routing\ModuleRoutingCallback;
use Honeybee\Core\Log\ILogger;
use Honeybee\Core\Log\Logger;

/**
 * The BaseAction serves as the base action to all actions implemented inside of honeybee.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class BaseAction extends \AgaviAction implements ILogger
{
    /**
     * Default error handling for method Read (GET Requests)
     *
     * @param \AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     */
    public function handleError(\AgaviRequestDataHolder $parameters)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = implode(', ', $errMsg['errors']) . ': ' . $errMsg['message'];
        }

        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    public function isSecure()
    {
        return TRUE;
    }

    /**
     * Returns an array to use in an actions handleError or getDefaultViewName
     * methods to forward to the default Error404 success view.
     *
     * @param string message message to display to user
     * @param string title title to display to user
     *
     * @return array of modulename and action/view name
     */
    public function getNotFoundView($message, $title)
    {
        if (!empty($message))
        {
            $this->setAttribute('org.honeybee.error_404.message', $message);
        }

        if (!empty($title))
        {
            $this->setAttribute('org.honeybee.error_404.title', $title);
        }

        return array(
            \AgaviConfig::get('actions.error_404_module'), 
            \AgaviConfig::get('actions.error_404_action') . '/' . \AgaviConfig::get('actions.error_404_action') . 'Success'
        );
    }

    protected function getModule()
    {
        $module = $this->getContext()->getRequest()->getAttribute('module', ModuleRoutingCallback::ATTRIBUTE_NAMESPACE);

        if (!($module instanceof Module))
        {
            throw new \Exception(
                "Unable to determine the Honebee module for the current action's scope." . PHP_EOL .
                "Make sure that the Honeybee ModuleRoutingCallback is executed for the related route."
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
    protected function addError($argument, $message, $severity = \AgaviValidator::ERROR)
    {
        $validation_manager = $this->getContainer()->getValidationManager();
        $incident = new \AgaviValidationIncident(NULL, $severity);
        $incident->addError(new \AgaviValidationError($message, NULL, array($argument)));
        $validation_manager->addIncident($incident);

        return $incident;
    }

    // @codeCoverageIgnoreStart
    public function getLoggerName()
    {
        return 'default';
    }

    public function logTrace()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::TRACE, get_class($this), func_get_args());
    }

    public function logDebug()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::DEBUG, get_class($this), func_get_args());
    }

    public function logInfo()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::INFO, get_class($this), func_get_args());
    }

    public function logNotice()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::NOTICE, get_class($this), func_get_args());
    }

    public function logWarning()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::WARNING, get_class($this), func_get_args());
    }

    public function logError()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::ERROR, get_class($this), func_get_args());
    }

    public function logCritical()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::CRITICAL, get_class($this), func_get_args());
    }

    public function logAlert()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::ALERT, get_class($this), func_get_args());
    }

    public function logEmergency()
    {
        $this->getContext()->getLoggerManager()->logTo($this->getLoggerName(), \AgaviLogger::EMERGENCY, get_class($this), func_get_args());
    }
    // @codeCoverageIgnoreEnd
}
