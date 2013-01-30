<?php

namespace Honeybee\Agavi\ConfigHandler;

class DataSourceConfigHandler extends BaseConfigHandler
{
    /**
     * Holds the name of the data_source document schema namespace.
     */
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/honeybee/config/datasource/definition/1.0';

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
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'datasource');
        $config = $document->documentURI;

        $parsedSourceDefinitions = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $parsedSourceDefinitions = array_merge_recursive(
                $parsedSourceDefinitions,
                $this->parseSourceDefinitions(
                    $cfgNode->getChild('datasources')
                )
            );
        }

        $data = array('datasources' => $parsedSourceDefinitions);

        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }

    /**
     * Parse the given datasources node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $dataSourcesElement
     *
     * @return array
     */
    protected function parseSourceDefinitions(\AgaviXmlConfigDomElement $dataSourcesElement)
    {
        $parsedSourceDefinitions = array();
        
        foreach ($dataSourcesElement->getChildren('datasource') as $dataSourceElement)
        {
            $name = $dataSourceElement->getAttribute('name');
            $parsedSourceDefinitions[$name] = $this->parseSourceDefinition($dataSourceElement);
        }

        return $parsedSourceDefinitions;
    }

    protected function parseSourceDefinition(\AgaviXmlConfigDomElement $dataSourceElement)
    {
        $sourceDefinition = array();
        $sourceDefinition['name'] = $dataSourceElement->getAttribute('name');
        $sourceDefinition['class'] = $dataSourceElement->getAttribute('class');
        $sourceDefinition['description'] = $dataSourceElement->getChild('description')->getValue();
        $sourceDefinition['recordType'] = $dataSourceElement->getChild('recordType')->getValue();

        $settings = array();
        if (($settingsElement = $dataSourceElement->getChild('settings')))
        {
            $settings = $this->parseSettings($settingsElement);
        }
        $sourceDefinition['settings'] = $settings;

        return $sourceDefinition;
    }
}
