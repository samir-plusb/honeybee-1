<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WorkflowConfigHandler
 *
 * @author shrink0r
 */
class WorkflowConfigHandler extends AgaviReturnArrayConfigHandler
{
    public function execute(AgaviXmlConfigDomDocument $document)
    {
        return parent::execute($document);
        /*
        $document->setDefaultNamespace($this->getParameter('namespace_uri', ''));

		$data = array();
		foreach($document->getConfigurationElements() as $cfg)
        {
			$data = array_merge($data, $this->convertToArray($cfg, true));
		}

        var_dump($data);exit;
         */
    }
}

?>
