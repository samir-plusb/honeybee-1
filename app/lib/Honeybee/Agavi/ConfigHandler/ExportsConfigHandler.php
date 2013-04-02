<?php

namespace Honeybee\Agavi\ConfigHandler;

/**
 * ExportsConfigHandler parses configuration files that follow the honeybee exports markup.
 *
 * @author     Thorsten Schmitt-Rink
 */
class ExportsConfigHandler extends BaseConfigHandler
{
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/honeybee/exports/1.0';

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
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'exports');
        $config = $document->documentURI;
        $exports = array();

        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            $exportsNode = $cfgNode->getChild('exports');

            foreach ($exportsNode->get('export') as $exportNode)
            {
                $exportName = $exportNode->getAttribute('name');
                $exportClass = $exportNode->getAttribute('class');
                $exportDescription = $exportNode->getChild('description')->nodeValue;

                $settingsNode = $exportNode->getChild('settings');
                $filtersNode = $exportNode->getChild('filters');
                $exports[$exportName] = array(
                    'class' => $exportClass,
                    'settings' => $settingsNode ? $this->parseSettings($settingsNode) : array(),
                    'description' => $exportDescription,
                    'filters' => $filtersNode ? $this->parseFilters($filtersNode) : array()
                );
            }
        }

        $configCode = sprintf('return %s;', var_export($exports, TRUE));

        return $this->generate($configCode, $config);
    }

    protected function parseFilters($filtersNode)
    {
        $filters = array();
        foreach ($filtersNode->get('filter') as $filterNode)
        {
            $filterName = $filterNode->getAttribute('name');
            $filterClass = $filterNode->getAttribute('class');

            $settingsNode = $filterNode->getChild('settings');
            $filters[$filterName] = array(
                'class' => $filterClass,
                'settings' => $settingsNode ? $this->parseSettings($settingsNode) : array()
            );
        }

        return $filters;
    }
}
