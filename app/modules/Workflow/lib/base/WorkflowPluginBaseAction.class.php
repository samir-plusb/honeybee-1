<?php

/**
 * The base action from which all Workflow module actions inherit.
 */
class WorkflowPluginBaseAction extends ProjectBaseAction
{

    /**
     * Handles the Read request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRead(AgaviRequestDataHolder $rd)
    {
        return 'Success';
    }

    /**
     * Handles the Write request method.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>A string containing the view name associated
     *                     with this action; or</li>
     *                     <li>An array with two indices: the parent module
     *                     of the view to be executed and the view to be
     *                     executed.</li>
     *                   </ul>^
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        return 'Success';
    }

    /**
     * handle validation errors and preprare plugin result data with an error message
     *
     * (non-PHPdoc)
     * @see ProjectBaseAction::handleError()
     */
    public function handleError(AgaviRequestDataHolder $rd)
    {
        $container = $this->getContainer();

        $message = 'Error in '.get_class($this);
        $validation_manager = $container->getValidationManager();
        if ($validation_manager->getReport()->hasIncidents())
        {
            $errors = $validation_manager->getReport()->getErrors();
            /* @var $error AgaviValidationError */
            $error = $errors[0];
            $message = sprintf('%s (%s :: %s)', $message, implode(', ',$error->getFields()), $error->getMessage());
        }

        WorkflowBaseInteractivePlugin::setPluginResultAttributes(
            $container,
            WorkflowInteractivePluginResult::STATE_ERROR,
            WorkflowInteractivePluginResult::GATE_NONE,
            $message);

        return parent::handleError($rd);
    }

}

?>