<?php

namespace Honeybee\Agavi\ConfigHandler;

class DataImportConfigHandler extends BaseConfigHandler
{
    /**
     * Holds the name of the data_import document schema namespace.
     */
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/midas/config/dataimport/definition/1.0';

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
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'dataimport');
        $config = $document->documentURI;

        $parsedImportDefinitions = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $parsedImportDefinitions = array_merge_recursive(
                $parsedImportDefinitions,
                $this->parseImportDefinitions(
                    $cfgNode->getChild('dataimports')
                )
            );
        }

        $data = array('dataimports' => $parsedImportDefinitions);
        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }

    /**
     * Parse the given dataimports node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $dataImportsElement
     *
     * @return array
     */
    protected function parseImportDefinitions(\AgaviXmlConfigDomElement $dataImportsElement)
    {
        $parsedImportDefinitions = array();
        
        foreach ($dataImportsElement->getChildren('dataimport') as $dataImportElement)
        {
            $name = $dataImportElement->getAttribute('name');
            $parsedImportDefinitions[$name] = $this->parseImportDefinition($dataImportElement);
        }

        return $parsedImportDefinitions;
    }

    protected function parseImportDefinition(\AgaviXmlConfigDomElement $dataImportElement)
    {
        $importDefinition = array();
        $importDefinition['name'] = $dataImportElement->getAttribute('name');
        $importDefinition['class'] = $dataImportElement->getAttribute('class');
        $importDefinition['description'] = $dataImportElement->getChild('description')->getValue();

        $settings = array();
        if (($settingsElement = $dataImportElement->getChild('settings')))
        {
            $settings = $this->parseSettings($settingsElement);
        }
        $importDefinition['settings'] = $settings;

        $datasources = array();
        if (($datasourcesElement = $dataImportElement->getChild('datasources')))
        {
            foreach ($datasourcesElement->get('datasource') as $datasourceElement)
            {
                $datasources[] = trim($datasourceElement->getValue());
            }
        }
        $importDefinition['datasources'] = $datasources;

        return $importDefinition;
    }
}
