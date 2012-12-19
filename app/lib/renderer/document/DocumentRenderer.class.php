<?php

abstract class DocumentRenderer implements IRenderer
{
    private $module;

    private $factory;

    abstract protected function doRender(HoneybeeDocument $document);

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
        $this->factory = new FieldRendererFactory($module);
    }

    public function render($payload)
    {
        if (! $payload instanceof HoneybeeDocument)
        {
            throw new InvalidArgumentException(
                "Only the type HoneybeeDocument may be passed to this function!"
            );
        }

        return $this->doRender($payload);
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    public function getRouteLink($name)
    {
        $prefix = $this->getModule()->getOption('prefix');
        $route = sprintf('%s.%s', $prefix, $name);
        
        return AgaviContext::getInstance()->getRouting()->gen($route);
    }

    public function getTranslationDomain()
    {
        return $this->getModule()->getOption('prefix') . '.rendering';
    }
}
