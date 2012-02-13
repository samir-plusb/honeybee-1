<?php

/**
 * The Workflow_SetupAction is repsonseable for setting up the Workflow module.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Workflow
 * @subpackage      Mvc
 */
class Workflow_SetupAction extends ProjectBaseAction
{
    /**
     * Execute the write logic for this action, hence process the given asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $moduleSetup = new WorkflowModuleSetup();

        try
        {
            $moduleSetup->setup();
        }
        catch (Exception $e)
        {
            throw $e;
            $this->setAttribute('errors', array($e->getMessage()));

            return 'Error';
        }

        return 'Success';
    }

}

?>