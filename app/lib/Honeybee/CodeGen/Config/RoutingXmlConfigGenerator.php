<?php

namespace Honeybee\CodeGen\Config;

class RoutingXmlConfigGenerator extends DefaultXmlConfigGenerator
{
    public function generate($name, array $filesToInclude)
    {
        $document = $this->createDocument($name);
        $root = $document->documentElement;

        $webConfig = $document->createElement('ae:configuration');
        $webConfig->setAttribute('context', 'web');
        $root->appendChild($webConfig);

        $consoleConfig = $document->createElement('ae:configuration');
        $consoleConfig->setAttribute('context', 'console');
        $root->appendChild($consoleConfig);

        $document->appendChild($root);

        $webRoutesNode = $document->createElement('routes');
        $webConfig->appendChild($webRoutesNode);

        $consoleRoutesNode = $document->createElement('routes');
        $consoleConfig->appendChild($consoleRoutesNode);

        foreach ($filesToInclude as $configFile)
        {
            $webRoutesNode->appendChild(
                $this->createWebRouting($document, $configFile)
            );
            $consoleRoutesNode->appendChild(
                $this->createConsoleRouting($document, $configFile)
            );
        }

        $this->writeConfigFile($document, $name);
    }

    protected function createConsoleRouting(\DOMDocument $document, $configFile)
    {
        $moduleName = $this->extractModuleNameFromPath($configFile);
        $moduleDefinition = str_replace('/', DIRECTORY_SEPARATOR,
             \AgaviConfig::get('core.modules_dir').'/'.$moduleName.'/config/dat0r/module.xml'
        );
        $modulePrefix = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $moduleName));
        $moduleRoute = $document->createElement('route');
        $moduleRoute->setAttribute('pattern', "^" . $modulePrefix . ".");
        $moduleRoute->setAttribute('module', $moduleName);

        if (file_exists($moduleDefinition))
        {
            $callbacks = $document->createElement('callbacks');
            $callback = $document->createElement('callback');
            $callback->setAttribute('class', 'Honeybee\\Agavi\\Routing\\ModuleRoutingCallback');
            $callbacks->appendChild($callback);

            $parameter = $document->createElement('ae:parameter', sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $moduleName));
            $parameter->setAttribute('name', 'implementor');

            $moduleRoute->appendChild($parameter);
            $moduleRoute->appendChild($callbacks);
        }

        $consoleInclude = $document->createElement('xi:include');
        $consoleInclude->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'),
            '../..',
            $configFile
        ));

        $consoleInclude->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xmlns(r=http://agavi.org/agavi/config/parts/routing/1.0) xpointer(/ae:configurations/ae:configuration[@context='console']/r:routes/*)"
        );

        $moduleRoute->appendChild($consoleInclude);

        return $moduleRoute;
    }

    protected function createWebRouting(\DOMDocument $document, $configFile)
    {
        $moduleName = $this->extractModuleNameFromPath($configFile);
        $moduleDefinition = str_replace('/', DIRECTORY_SEPARATOR,
             \AgaviConfig::get('core.modules_dir').'/'.$moduleName.'/config/dat0r/module.xml'
        );

        $modulePrefix = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $moduleName));
        $moduleRoute = $document->createElement('route');
        $moduleRoute->setAttribute('name', $modulePrefix);
        $moduleRoute->setAttribute('pattern', '^/' . $modulePrefix . '/');
        $moduleRoute->setAttribute('module', $moduleName);

        if (file_exists($moduleDefinition))
        {
            $callbacks = $document->createElement('callbacks');
            $callback = $document->createElement('callback');
            $callback->setAttribute('class', 'Honeybee\\Agavi\\Routing\\ModuleRoutingCallback');
            $callbacks->appendChild($callback);

            $parameter = $document->createElement(
                'ae:parameter',
                sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $moduleName)
            );
            $parameter->setAttribute('name', 'implementor');

            $moduleRoute->appendChild($parameter);
            $moduleRoute->appendChild($callbacks);
        }

        $webInclude = $document->createElement('xi:include');
        $webInclude->setAttribute('href', str_replace(
            \AgaviConfig::get('core.app_dir'),
            '../..',
            $configFile
        ));
        $webInclude->setAttribute(
            'xpointer',
            "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xmlns(r=http://agavi.org/agavi/config/parts/routing/1.0) xpointer(//ae:configuration[@context='web']/r:routes/*)"
        );

        $moduleRoute->appendChild($webInclude);

        return $moduleRoute;
    }

    protected function extractModuleNameFromPath($path)
    {
        return str_replace(
            '/config/routing.xml',
            '',
            str_replace(
                \AgaviConfig::get('core.app_dir').'/modules/', '', $path
            )
        );
    }
}
