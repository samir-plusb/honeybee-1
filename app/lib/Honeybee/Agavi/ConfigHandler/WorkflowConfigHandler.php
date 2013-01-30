<?php

namespace Honeybee\Agavi\ConfigHandler;

/**
 * WorkflowConfigHandler parses configuration files that follow the honeybee workflow markup.
 *
 * @author     Thorsten Schmitt-Rink
 */
class WorkflowConfigHandler extends \AgaviXmlConfigHandler
{
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/honeybee/workflow/1.0';

    /**
     * Execute this configuration handler.
     *
     * @param      string An absolute filesystem path to a configuration file.
     * @param      string An optional context in which we are currently running.
     *
     * @return     string Data to be written to a cache file.
     *
     * @throws     <b>AgaviUnreadableException</b> If a requested configuration
     *                                             file does not exist or is not
     *                                             readable.
     * @throws     <b>AgaviParseException</b> If a requested configuration file is
     *                                        improperly formatted.
     */
    public function execute(\AgaviXmlConfigDomDocument $document)
    {
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'workflow');
        $config = $document->documentURI;
        $data = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $workflow = $cfgNode->getChild('workflow');
            $parsedSteps = array();
            /* @var $stepNode AgaviXmlConfigDomElement */
            foreach ($workflow->getChild('steps')->get('step') as $stepNode)
            {
                $pluginNode = $stepNode->getChild('plugin');
                $parsedGates = $this->parseGates($pluginNode->getChild('gates'));
                $parsedSteps[$stepNode->getAttribute('name')] = array(
                    'description' => $stepNode->getChild('description')->nodeValue,
                    'plugin' => array(
                        'type' => $pluginNode->getAttribute('type'),
                        'gates' => $parsedGates,
                        'parameters' => $pluginNode->getAgaviParameters()
                    )
                );
            }
            $data['workflow'] = array(
                'name' => $workflow->getAttribute('name'),
                'description' => $workflow->getChild('description')->nodeValue,
                'start_at' => $workflow->getChild('start_at')->nodeValue,
                'steps' => $parsedSteps
            );
        }
        $this->verifyWorkflowLogic($data);
        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }

    /**
     * Grab the gate definitions from the given gates container
     * and return a common structure for representing gate data towards the code using the config (WorkflowHandler).
     *
     * @param AgaviXmlConfigDomElement $gatesNode
     * @return array
     */
    protected function parseGates(\AgaviXmlConfigDomElement $gatesNode)
    {
        $parsedGates = array();
        foreach (array('step', 'workflow', 'end') as $gateType)
        {
            /* @var $gateNode AgaviXmlConfigDomElement */
            foreach ($gatesNode->get('gate_' . $gateType) as $gateNode)
            {
                $gateTarget = trim($gateNode->nodeValue);
                $gateData = array('type' => $gateType);
                if ('end' !== $gateType)
                {
                    $gateData['target'] = empty($gateTarget) ? NULL : $gateTarget;
                }
                $parsedGates[$gateNode->getAttribute('name')] = $gateData;
            }
        }
        return $parsedGates;
    }

    /**
     * Verify that the given the workflow definition.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function verifyWorkflowLogic(array $data) // @codingStandardsIgnoreEnd
    {
        // @todo Check if all gates refer to existing targets (steps, workflows, etc)
        // and throw an AgaviParseException if not.
    }

}
