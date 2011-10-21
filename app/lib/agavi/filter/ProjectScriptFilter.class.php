<?php

class ProjectScriptFilter extends AgaviFilter implements AgaviIGlobalFilter
{
	const ENCODING_UTF_8 = 'utf-8';

	const ENCODING_ISO_8859_1 = 'iso-8859-1';

    protected static $viewPathSearch = array('.', '*');

    protected static $viewPathReplace = array('\.', '.*');

	/**
	 * @var        DOMDocument Our (X)HTML document.
	 */
	protected $doc;

	/**
	 * @var        DOMXPath Our XPath instance for the document.
	 */
	protected $xpath;

	/**
	 * @var        string The XML NS prefix we're working on with XPath, including
	 *                    a colon (or empty string if document has no NS).
	 */
	protected $xmlnsPrefix = '';

    protected $scriptConfig;

    protected $loadedScripts = array();

    protected $loadedPackages = array();

    protected $packscripts = TRUE;

    protected $jsCacheDir;

    protected $cssCacheDir;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->packscripts = (isset($parameters['pack_scripts']) && TRUE === $parameters['pack_scripts']);
        $configDir = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR;
        $this->scriptConfig = include AgaviConfigCache::checkConfig($configDir . 'scripts.xml');

        $this->cssCacheDir = $parameters['css_cache'];
        $this->jsCacheDir = $parameters['js_cache'];
    }

	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
        $filterChain->execute($container);
		$response = $container->getResponse();
        $output = NULL;

		if(! $response->isContentMutable() || ! ($output = $response->getContent()))
        {
            // throw exception? we cant really live without the scripts.
			return FALSE;
		}

        $this->loadDom($output);

        list($javascripts, $stylesheets) = $this->loadScripts(
            $this->buildViewPath($container)
        );

        if ($this->packscripts)
        {
            $this->addJavascripts(
                $this->packJavascripts($javascripts)
            );
            $this->addStylesheets(
                $this->packStylesheets($stylesheets)
            );
        }
        else
        {
            $this->addStylesheets($stylesheets);
            $this->addJavascripts($javascripts);
        }

        $container->getResponse()->setContent(
            $this->doc->saveHTML()
        );
    }

    public function getJsCacheDir()
    {
        return $this->jsCacheDir;
    }

    public function getCssCacheDir()
    {
        return $this->cssCacheDir;
    }

    protected function loadDom($content)
    {
        $this->doc = new DOMDocument();
        $this->doc->formatOutput = TRUE;
        $this->doc->preserveWhitespace = TRUE;

        if (! $this->doc->loadXML(html_entity_decode($content, null, self::ENCODING_UTF_8)))
        {
            // maybe just log the error and return silently?
            throw new Exception("Unable to parse content.");
        }

        $this->xpath = new DOMXPath($this->doc);

        if($this->doc->documentElement && $this->doc->documentElement->namespaceURI)
        {
            $this->xpath->registerNamespace('html', $this->doc->documentElement->namespaceURI);
            $this->xmlnsPrefix = 'html:';
        }
        else
        {
            $this->xmlnsPrefix = '';
        }
    }

    protected function buildViewPath(AgaviExecutionContainer $container)
    {
        $module = strtolower($container->getModuleName());
        $action = strtolower($container->getActionName());
        $viewParts = explode('/', $container->getViewName());
        $view = strtolower(
            str_replace($viewParts[0], '', array_pop($viewParts))
        );

        return implode('.', array($module, $action, $view));
    }

    protected function loadScripts($viewpath)
    {
        $deployData = $this->loadDeployData($viewpath);

        $javascripts = array();
        $stylesheets = array();

        foreach ($deployData['packages'] as $packageName)
        {
            $package = $this->scriptConfig['packages'][$packageName];
            foreach ($package['javascripts'] as $javascript)
            {
                if (! in_array($javascript, $javascripts))
                {
                    $javascripts[] = $javascript;
                }
            }

            foreach ($package['stylesheets'] as $stylesheet)
            {
                if (! in_array($stylesheet, $stylesheets))
                {
                    $stylesheets[] = $stylesheet;
                }
            }
        }

        $javascripts = array_merge($javascripts, $deployData['javascripts']);
        $stylesheets = array_merge($stylesheets, $deployData['stylesheets']);

        return array($javascripts, $stylesheets);
    }

    protected function loadDeployData($viewpath)
    {
        $affectedPackages = array();
        $affectedJavascripts = array();
        $affectedStylesheets = array();

        foreach ($this->scriptConfig['deployments'] as $curViewpath => $deploymentInfo)
        {
            $escapedPath = str_replace(
                self::$viewPathSearch,
                self::$viewPathReplace,
                $curViewpath
            );
            $pattern = sprintf('#^%s$#is', $escapedPath);

            if (preg_match($pattern, $viewpath))
            {
                foreach ($deploymentInfo['packages'] as $packageName)
                {
                    $this->loadPackage($packageName, $affectedPackages);
                }

                foreach ($deploymentInfo['javascripts'] as $javascript)
                {
                    if (! in_array($javascript, $affectedJavascripts))
                    {
                        $affectedJavascripts[] = $javascript;
                    }
                }

                foreach ($deploymentInfo['stylesheets'] as $stylesheet)
                {
                    if (! in_array($stylesheet, $affectedStylesheets))
                    {
                        $affectedStylesheets[] = $stylesheet;
                    }
                }
            }
        }

        $deploy_data = array(
            'packages' => array(),
            'javascripts' => $affectedJavascripts,
            'stylesheets' => $affectedStylesheets
        );
        /**
         * Make sure we have our loaded packages in the exact same order
         * as defined inside our scripts.xml config.
         */
        foreach (array_keys($this->scriptConfig['packages']) as $packageName)
        {
            if (in_array($packageName, $affectedPackages))
            {
                $deploy_data['packages'][] = $packageName;
            }
        }

        return $deploy_data;
    }

    protected function loadPackage($packageName, array & $loadedPackages)
    {
        if (! isset($this->scriptConfig['packages'][$packageName]))
        {
            throw new Exception(
                sprintf(
                    "Encountered undefined script package: '%s'",
                    $packageName
                )
            );
        }

        if (! in_array($packageName, $loadedPackages))
        {
            $package = $this->scriptConfig['packages'][$packageName];
            $loadedPackages[] = $packageName;

            foreach ($package['deps'] as $depPackage)
            {
                $this->loadPackage($depPackage, $loadedPackages);
            }
        }
    }

    protected function packJavascripts(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = realpath(
            dirname(AgaviConfig::get('core.app_dir')) . '/pub/'
        );
        $deployPath =realpath($this->jsCacheDir) . DIRECTORY_SEPARATOR . $deployHash . '.js';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packedJs = $script_packer->pack($scripts, 'js');

            file_put_contents($deployPath, $packedJs);
        }

        return array($pubPath);
    }

    protected function packStylesheets(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = realpath(
            dirname(AgaviConfig::get('core.app_dir')) . '/pub/'
        );
        $deployPath = realpath($this->cssCacheDir) . DIRECTORY_SEPARATOR . $deployHash . '.css';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packedCss = $script_packer->pack(
                $this->adjustRelativeCssPaths($scripts),
                'css'
            );

            file_put_contents($deployPath, $packedCss);
        }

        return array($pubPath);
    }

    protected function adjustRelativeCssPaths(array $cssFiles)
    {
        $stylesheets = array();

        foreach ($cssFiles as $cssFile)
        {
            $that = $this;

            $replaceCallback = function (array $matches) use ($that, $cssFile)
            {
                $pubDir = realpath(
                    dirname(AgaviConfig::get('core.app_dir')) . '/pub/'
                );
                $cacheDir = realpath($that->getCssCacheDir()) . DIRECTORY_SEPARATOR;
                $cacheRelPath = substr(str_replace($pubDir, '', $cacheDir), 1);

                $filename = basename($cssFile);
                $dirName = dirname($cssFile) . DIRECTORY_SEPARATOR;
                $srcRelpath = str_replace($pubDir, '', $dirName);

                $pubPath = $srcRelpath . $filename;
                $srcDepth = count(explode(DIRECTORY_SEPARATOR, $srcRelpath)) - 1;
                $cacheDepth = count(explode(DIRECTORY_SEPARATOR, $cacheRelPath)) - 1;
                $newPath = '';

                if ($srcDepth < $cacheDepth)
                {
                    for ($i = $cacheDepth - $srcDepth; $i > 0; $i--)
                    {
                        $newPath .= '../';
                    }
                }

                $newPath .= $matches[1];

                if ($srcDepth > $cacheDepth)
                {
                    for ($i = $srcDepth - $cacheDepth; $i > 0; $i--)
                    {
                        $newPath = substr($newPath, strpos($newPath, '../'));
                    }
                }

                return sprintf("url('%s')", $newPath);
            };

            // @todo replace all possible @import and possible urls.
            $adjustedCss = preg_replace_callback(
                '#url\([\'"](?!http|/|data)(.*?)[\'"]\)#i',
                $replaceCallback,
                file_get_contents($cssFile)
            );

            $tmpPath = tempnam(sys_get_temp_dir(), 'css.adjust.');
            file_put_contents($tmpPath, $adjustedCss);
            $stylesheets[] = $tmpPath;
        }

        return $stylesheets;
    }

    protected function calculateDeployHash(array $scripts)
    {
        $lastModified = 0;
        $hashBase = '';

        foreach ($scripts as $javascript)
        {
            $mTime = filemtime($javascript);
            $hashBase .= $javascript;

            if ($lastModified < $mTime)
            {
                $lastModified = $mTime;
            }
        }

        return sha1($hashBase . $lastModified);
    }

    protected function addStylesheets($stylesheets)
    {
        $query_result = $this->xpath->query(
            sprintf('//%shead', $this->xmlnsPrefix)
        );
        $head = $query_result->item(0);

        foreach ($stylesheets as $stylesheet)
        {
            $link = $this->doc->createElement('link');
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('type', 'text/css');
            $link->setAttribute('href', $stylesheet);

            $head->appendChild($link);
        }
    }

    protected function addJavascripts($javascripts)
    {
        $query_result = $this->xpath->query(
            sprintf('//%sbody', $this->xmlnsPrefix)
        );
        $body = $query_result->item(0);

        foreach ($javascripts as $javascript)
        {
            $script = $this->doc->createElement('script');
            $script->setAttribute('type', 'text/javascript');
            $script->setAttribute('src', $javascript);

            $body->appendChild($script);
        }
    }
}

?>