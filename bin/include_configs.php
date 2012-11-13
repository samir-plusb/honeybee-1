<?php

$rootDir = dirname(dirname(__FILE__));
$default_context = 'web';
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';
require(
    str_replace(
        '/', DIRECTORY_SEPARATOR, 
        $rootDir.'/vendor/agavi/agavi/src/build/agavi/build.php'
    )
);
AgaviBuild::bootstrap();


class RoutingConfigGenerator extends DefaultConfigGenerator
{
    public function generate($name, array $filesToInclude)
    {
        $configIncludeDir = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 
            'includes' . DIRECTORY_SEPARATOR;

        $document = $this->createDocument($name);
        $root = $document->documentElement;
        $webConfig = $document->createElement('ae:configuration');
        $webConfig->setAttribute('context', 'web');
        $root->appendChild($webConfig);

        $consoleConfig = $document->createElement('ae:configuration');
        $consoleConfig->setAttribute('context', 'console');
        $root->appendChild($consoleConfig);

        $document->appendChild($root);
        $routesNode = $document->createElement('routes');
        $webConfig->appendChild($routesNode);

        foreach ($filesToInclude as $configFile)
        {
            $moduleName = str_replace(
                '/config/routing.xml', 
                '', 
                str_replace(
                    AgaviConfig::get('core.app_dir').'/modules/', '', $configFile
                )
            );
            
            $moduleRoute = $document->createElement('route');
            $moduleRoute->setAttribute('name', strtolower($moduleName));
            $moduleRoute->setAttribute('pattern', '^/' . strtolower($moduleName));
            $moduleRoute->setAttribute('module', $moduleName);

            $webInclude = $document->createElement('xi:include');
            $webInclude->setAttribute('href', str_replace(
                AgaviConfig::get('core.app_dir'), 
                '../..', 
                $configFile
            ));
            $webInclude->setAttribute(
                'xpointer',
                "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xmlns(r=http://agavi.org/agavi/config/parts/routing/1.0) xpointer(//ae:configuration[@context='web']/r:routes/r:route)/"
            );
            $moduleRoute->appendChild($webInclude);
            $routesNode->appendChild($moduleRoute);

            $consoleInclude = $document->createElement('xi:include');
            $consoleInclude->setAttribute('href', str_replace(
                AgaviConfig::get('core.app_dir'),
                '../..',
                $configFile
            ));
            $consoleInclude->setAttribute(
                'xpointer',
                "xmlns(ae=http://agavi.org/agavi/config/global/envelope/1.0) xpointer(/ae:configurations/ae:configuration[@context='console'])/"
            );
            $consoleConfig->appendChild($consoleInclude);
        }

        $includeFile = $configIncludeDir . $name . '.xml';
        $document->formatOutput = TRUE;
        $document->save($includeFile);
    }
}

//
//    RUNTIME
//
$scanner = new ConfigurationScanner();
foreach ($scanner->scan() as $name => $files)
{
    if ('routing' !== $name)
    {
        $generator = new DefaultConfigGenerator();
        $generator->generate($name, $files);
    }
    else
    {
        $generator = new RoutingConfigGenerator();
        $generator->generate($name, $files);
    }
}

exit(0);
