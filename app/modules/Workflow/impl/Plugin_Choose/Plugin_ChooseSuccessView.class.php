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
        $gate = $parameters->getParameter('gate', WorkflowPluginResult::GATE_NONE);
        $state = WorkflowPluginResult::STATE_EXPECT_INPUT;
        $message = '';

        if ($parameters->hasParameter('gate'))
        {
            $message = sprintf("Gate choosen: %d\n", $parameters->getParameter('gate'));
            $state = WorkflowPluginResult::STATE_OK;
        }
        else
        {
            $message = "Choose a gate\n\n";
            foreach ($this->getContainer()->getParameter('gates') as $idx => $label)
            {
                $message .= sprintf("%d := %s\n", $idx, $label);
            }
            $routes = $this->getContext()->getRouting()->gen(NULL);
            $message .= sprintf(
                "\nExecute %s --ticket %s --gate [NUMBER]\n",
                $routes[0],
                $parameters->getParameter('ticket')->getIdentifier()
            );
        }

        $pluginResult = $this->getContainer()->getAttribute(
            WorkflowBaseInteractivePlugin::ATTR_RESULT,
            WorkflowBaseInteractivePlugin::NS_PLUGIN_ATTRIBUTES
        );
        $pluginResult->setGate($gate);
        $pluginResult->setState($state);
        $pluginResult->setMessage($message);

        return $message;
    }
}
