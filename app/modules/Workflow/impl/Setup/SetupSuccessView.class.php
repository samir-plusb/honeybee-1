<?php

/**
 * Setup module environment in couch data base
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Setup
 */
class Workflow_Setup_SetupSuccessView extends ProjectWorkflowBaseView
{
    /**
     * Handles the Text output type.
     *
     * @parameter  AgaviRequestDataHolder the (validated) request data
     *
     * @return     mixed <ul>
     *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
     *                     <li>Any other type will be set as the response content.</li>
     *                   </ul>
     */
    public function executeText(AgaviRequestDataHolder $rd)
    {
        return "Setup done\n";
    }
}

?>