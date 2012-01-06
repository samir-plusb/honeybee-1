<?php
/**
 * WorkflowConfigHandler parses configuration files that follow the midas workflow markup.
 *
 * @package    Workflow
 * @subpackage Config
 *
 * @author     Thorsten Schmitt-Rink
 * @copyright  The Agavi Project
 *
 * @version    $Id:$
 */
class WorkflowConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://berlinonline.de/schemas/midas/workflow/1.0';

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
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'workflow');
		$config = $document->documentURI;
        $data = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
		foreach($document->getConfigurationElements() as $cfgNode)
        {
            $workflow = $cfgNode->getChild('workflow');
            $parsedSteps = array();
            /* @var $stepNode AgaviXmlConfigDomElement */
            foreach ($workflow->getChild('steps')->get('step') as $stepNode)
            {
                $pluginNode = $stepNode->getChild('plugin');
                $parsedGates = array();
                /* @var $gateNode AgaviXmlConfigDomElement */
                foreach ($pluginNode->getChild('gates')->get('gate') as $gateNode)
                {
                    $gateTarget = trim($gateNode->nodeValue);
                    $parsedGates[$gateNode->getAttribute('name')] = empty($gateTarget) ? NULL : $gateTarget;
                }
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
        $this->verifyWorkflowLogic();
        $configCode = sprintf('return %s;', var_export($data, true));
		return $this->generate($configCode, $config);
	}

    /**
     * Verify that the given the workflow definition
     */
    protected function verifyWorkflowLogic()
    {
        // @todo Check if all gates refer to existing steps etc. Throw an AgaviParseException if not.
    }
}

?>
