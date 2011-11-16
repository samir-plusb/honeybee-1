<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 */
class Workflow_Plugin_Choose_Plugin_ChooseSuccessView extends WorkflowPluginBaseView
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
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        if ($parameters->hasParameter('gate'))
        {
            $response = sprintf("Gate choosen: %d\n", $parameters->getParameter('gate'));

            WorkflowBaseInteractivePlugin::setPluginResultAttributes(
                $this->getContainer(),
                WorkflowInteractivePluginResult::STATE_OK,
                $parameters->getParameter('gate'));
        }
        else
        {
            $response = "Choose a gate\n\n";
            foreach ($this->getContainer()->getParameter('gates') as $idx => $label)
            {
                $response .= sprintf("%d := %s\n", $idx, $label);
            }
            $routes = $this->getContext()->getRouting()->gen(NULL);
            $response .= sprintf("\nExecute %s --ticket %s --gate [NUMBER]\n",
                $routes[0], $parameters->getParameter('ticket')->getIdentifier());

            WorkflowBaseInteractivePlugin::setPluginResultAttributes(
                $this->getContainer(),
                WorkflowInteractivePluginResult::STATE_EXPECT_INPUT);
        }

        return $response;
    }
}
