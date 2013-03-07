<?php

namespace Honeybee\Agavi\ConfigHandler;

class ImportConsumerConfigHandler extends BaseConfigHandler
{
    /**
     * Holds the name of the data_import document schema namespace.
     */
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/honeybee/config/consumer/definition/1.0';

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
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'consumer');
        $config = $document->documentURI;

        $parsedConsumerDefinitions = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $parsedConsumerDefinitions = array_merge_recursive(
                $parsedConsumerDefinitions,
                $this->parseConsumerDefinitions(
                    $cfgNode->getChild('consumers')
                )
            );
        }

        $data = array('consumers' => $parsedConsumerDefinitions);
        $configCode = sprintf('return %s;', var_export($data, TRUE));

        return $this->generate($configCode, $config);
    }

    /**
     * Parse the given consumers node and return the corresponding array representation.
     *
     * @param AgaviXmlConfigDomElement $consumersElement
     *
     * @return array
     */
    protected function parseConsumerDefinitions(\AgaviXmlConfigDomElement $consumersElement)
    {
        $parsedConsumerDefinitions = array();
        
        foreach ($consumersElement->getChildren('consumer') as $consumerElement)
        {
            $name = trim($consumerElement->getAttribute('name'));
            $parsedConsumerDefinitions[$name] = $this->parseConsumerDefinition($consumerElement);
        }

        return $parsedConsumerDefinitions;
    }

    protected function parseConsumerDefinition(\AgaviXmlConfigDomElement $consumerElement)
    {
        $settings = array();
        if (($settingsElement = $consumerElement->getChild('settings')))
        {
            $settings = $this->parseSettings($settingsElement);
        }

        $filters = array();
        foreach ($consumerElement->getChild('filters')->get('filter') as $filterElement)
        {
            $filterSettings = array();
            if (($filterSettingsElement = $filterElement->getChild('settings')))
            {
                $filterSettings = $this->parseSettings($filterSettingsElement);
            }

            $filters[] = array(
                'name' => trim($filterElement->getAttribute('name')),
                'class' => trim($filterElement->getAttribute('class')),
                'settings' => $filterSettings
            );
        }

        return array(
            'name' => trim($consumerElement->getAttribute('name')),
            'description' => trim($consumerElement->getChild('description')->getValue()),
            'class' => trim($consumerElement->getAttribute('class')),
            'settings' => $settings,
            'filters' => $filters, 
            'provider' => trim(
                $consumerElement->getChild('provider')->getAttribute('ref')
            )
        );
    }
}
