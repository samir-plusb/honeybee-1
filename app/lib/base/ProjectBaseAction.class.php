<?php

/**
 * The ProjectBaseAction serves as the base action to all actions implemented inside of this project.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Mvc
 */
class ProjectBaseAction extends AgaviAction
{

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

    /**
     * Default error handling for all methods
     *
     * @param AgaviRequestDataHolder $parameters
     * @return array (modulename, viewname)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleError(AgaviRequestDataHolder $parameters)
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

}
