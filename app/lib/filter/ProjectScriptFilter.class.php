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

	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
        if (! $this->initDocument($filterChain, $container))
        {
            return;
        }

        $view_path = $this->buildViewPath($container);
        $this->processStylesheets($view_path);
        $this->processJavascripts($view_path);
        $container->getResponse()->setContent($this->doc->saveHTML());
    }

    protected function initDocument(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
    {
        $filterChain->execute($container);
		$response = $container->getResponse();
        $output = NULL;

		if(! $response->isContentMutable() || ! ($output = $response->getContent()))
        {
			return FALSE;
		}

        $this->doc = new DOMDocument();
        $this->doc->formatOutput = TRUE;
        $this->doc->preserveWhitespace = TRUE;

        if (! $this->doc->loadXML(html_entity_decode($output, null, self::ENCODING_UTF_8)))
        {
            // maybe just log the error and return silently?
            throw new Exception("Unable to parse output.");
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

        $config_dir = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR;
        $this->script_config = include AgaviConfigCache::checkConfig($config_dir . 'scripts.xml');

        return TRUE;
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

    protected function processStylesheets($view_path)
    {
        $query_result = $this->xpath->query(
            sprintf('//%shead', $this->xmlnsPrefix)
        );
        $head = $query_result->item(0);

        foreach ($this->script_config[ProjectScriptsConfigHandler::PACKAGE_CSS] as $view => $stylesheets)
        {
            $view_name = str_replace(
                self::$view_path_search,
                self::$view_path_replace,
                $view
            );
            $pattern = sprintf('~^%s$~is', $view_name);

            if (preg_match($pattern, $view_path))
            {
                $this->addStylesheets($head, $stylesheets);
            }
        }
    }

    protected function addStylesheets($head, $stylesheets)
    {
        foreach ($stylesheets as $stylesheet)
        {
            $link = $this->doc->createElement('link');
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('type', 'text/css');
            $link->setAttribute('href', $stylesheet);

            $head->appendChild($link);
        }
    }

    protected function processJavascripts($view_path)
    {
        $query_result = $this->xpath->query(
            sprintf('//%sbody', $this->xmlnsPrefix)
        );
        $body = $query_result->item(0);

        foreach ($this->script_config[ProjectScriptsConfigHandler::PACKAGE_JS] as $view => $javascripts)
        {
            $view_name = str_replace(
                self::$view_path_search,
                self::$view_path_replace,
                $view
            );
            $pattern = sprintf('~^%s$~is', $view_name);

            if (preg_match($pattern, $view_path))
            {
                $this->addJavascripts($body, $javascripts);
            }
        }
    }

    protected function addJavascripts($body, $scripts)
    {
        foreach ($scripts as $javascript)
        {
            $script = $this->doc->createElement('script');
            $script->setAttribute('type', 'text/javascript');
            $script->setAttribute('src', $javascript);

            $body->appendChild($script);
        }
    }
}

?>