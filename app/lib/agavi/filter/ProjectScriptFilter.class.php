<?php

class ProjectScriptFilter extends AgaviFilter implements AgaviIGlobalFilter
{
	const ENCODING_UTF_8 = 'utf-8';

	const ENCODING_ISO_8859_1 = 'iso-8859-1';

    protected static $view_path_search = array('.', '*');

    protected static $view_path_replace = array('\.', '.*');

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

    protected $script_config;

    protected $loaded_scripts = array();

    protected $loaded_packages = array();

    protected $packscripts = TRUE;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->packscripts = (isset($parameters['pack_scripts']) && TRUE === $parameters['pack_scripts']);
        $config_dir = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR;
        $this->script_config = include AgaviConfigCache::checkConfig($config_dir . 'scripts.xml');
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
        $view_parts = explode('/', $container->getViewName());
        $view = strtolower(
            str_replace($view_parts[0], '', array_pop($view_parts))
        );

        return implode('.', array($module, $action, $view));
    }

    protected function loadScripts($viewpath)
    {
        $deploy_data = $this->loadDeployData($viewpath);

        $javascripts = array();
        $stylesheets = array();

        foreach ($deploy_data['packages'] as $package_name)
        {
            $package = $this->script_config['packages'][$package_name];
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

        $javascripts = array_merge($javascripts, $deploy_data['javascripts']);
        $stylesheets = array_merge($stylesheets, $deploy_data['stylesheets']);

        return array($javascripts, $stylesheets);
    }

    protected function loadDeployData($viewpath)
    {
        $affected_packages = array();
        $affected_javascripts = array();
        $affected_stylesheets = array();

        foreach ($this->script_config['deployments'] as $cur_viewpath => $deployment_info)
        {
            $escaped_path = str_replace(
                self::$view_path_search,
                self::$view_path_replace,
                $cur_viewpath
            );
            $pattern = sprintf('#^%s$#is', $escaped_path);

            if (preg_match($pattern, $viewpath))
            {
                foreach ($deployment_info['packages'] as $package_name)
                {
                    $this->loadPackage($package_name, $affected_packages);
                }

                foreach ($deployment_info['javascripts'] as $javascript)
                {
                    if (! in_array($javascript, $affected_javascripts))
                    {
                        $affected_javascripts[] = $javascript;
                    }
                }

                foreach ($deployment_info['stylesheets'] as $stylesheet)
                {
                    if (! in_array($stylesheet, $affected_stylesheets))
                    {
                        $affected_stylesheets[] = $stylesheet;
                    }
                }
            }
        }

        $deploy_data = array(
            'packages' => array(),
            'javascripts' => $affected_javascripts,
            'stylesheets' => $affected_stylesheets
        );
        /**
         * Make sure we have our loaded packages in the exact same order
         * as defined inside our scripts.xml config.
         */
        foreach (array_keys($this->script_config['packages']) as $package_name)
        {
            if (in_array($package_name, $affected_packages))
            {
                $deploy_data['packages'][] = $package_name;
            }
        }

        return $deploy_data;
    }

    protected function loadPackage($package_name, array & $loaded_packages)
    {
        if (! isset($this->script_config['packages'][$package_name]))
        {
            throw new Exception(
                sprintf(
                    "Encountered undefined script package: '%s'",
                    $package_name
                )
            );
        }

        if (! in_array($package_name, $loaded_packages))
        {
            $package = $this->script_config['packages'][$package_name];
            $loaded_packages[] = $package_name;

            foreach ($package['deps'] as $dep_package)
            {
                $this->loadPackage($dep_package, $loaded_packages);
            }
        }
    }

    protected function packJavascripts(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $baseDir = dirname(AgaviConfig::get('core.app_dir')) . '/pub/';
        $pubPath =  'js/deploy/' . $deployHash . '.js';
        $deployPath = $baseDir . $pubPath;

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packed_js = $script_packer->pack($scripts, 'js');

            file_put_contents($deployPath, $packed_js);
        }

        return array($pubPath);
    }

    protected function packStylesheets(array $scripts)
    {
        $deployHash = $this->calculateDeployHash($scripts);
        $baseDir = dirname(AgaviConfig::get('core.app_dir')) . '/pub/';
        $pubPath =  'css/' . $deployHash . '.css';
        $deployPath = $baseDir . $pubPath;

        if (! file_exists($deployPath))
        {
            $script_packer = new ProjectScriptPacker();
            $packed_css = $script_packer->pack($scripts, 'css');

            file_put_contents($deployPath, $packed_css);
        }

        return array($pubPath);
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