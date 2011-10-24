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

    protected $loadedScripts = array();

    protected $loadedPackages = array();

    protected $config;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->config = new ProjectScriptFilterConfig($parameters);
    }

	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
        $filterChain->execute($container);
		$response = $container->getResponse();
        $output = NULL;

		if(! $response->isContentMutable() || ! ($output = $response->getContent()))
        {
            // throw exception? we cant really live without our scripts...
			return FALSE;
		}

        $curOutputType = $response->getOutputType()->getName();
        if (! $this->config->isOutputTypeSupported($curOutputType))
        {
            // ot not supported, log to info or debug?
            return FALSE;
        }

        $this->loadDom($output);

        list($javascripts, $stylesheets) = $this->loadScripts(
            $this->buildViewPath($container)
        );

        if ($this->config->isPackingEnabled())
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

    public function getConfig()
    {
        return $this->config;
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
            $package = $this->config->getPackageData($packageName);
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

        foreach ($this->config->getDeployments() as $curViewpath => $deploymentInfo)
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
        foreach ($this->config->getPackageNames() as $packageName)
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
        if (! in_array($packageName, $loadedPackages))
        {
            $package = $this->config->getPackageData($packageName);
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
        $pubDir = $this->config->get(ProjectScriptFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getJsCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.js';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packedJs = $script_packer->pack($scripts, 'js');

            array_map( "unlink", glob($this->config->getJsCacheDir().'/*.js')); // remove all prev caches
            file_put_contents($deployPath, $packedJs);
        }

        return array($pubPath);
    }

    protected function packStylesheets(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $pubDir = $this->config->get(ProjectScriptFilterConfig::CFG_PUB_DIR);
        $deployPath = $this->config->getCssCacheDir() . DIRECTORY_SEPARATOR . $deployHash . '.css';
        $pubPath = substr(str_replace($pubDir, '', $deployPath), 1);

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packedCss = $script_packer->pack(
                $this->adjustRelativeCssPaths($scripts),
                'css'
            );

            array_map( "unlink", glob($this->config->getCssCacheDir().'/*.css')); // remove all prev caches
            file_put_contents($deployPath, $packedCss);
        }

        return array($pubPath);
    }

    protected function adjustRelativeCssPaths(array $cssFiles)
    {
        $pubDir = $this->config->get(ProjectScriptFilterConfig::CFG_PUB_DIR);
        $cacheDir = realpath($this->config->getCssCacheDir()) . DIRECTORY_SEPARATOR;
        $cacheRelPath = substr(str_replace($pubDir, '', $cacheDir), 1);

        $stylesheets = array();
        foreach ($cssFiles as $cssFile)
        {
            $replaceCallback = function (array $matches) use ($pubDir, $cssFile, $cacheRelPath)
            {
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

            $tmpPath = tempnam(sys_get_temp_dir(), 'midas.adjust.css_');
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