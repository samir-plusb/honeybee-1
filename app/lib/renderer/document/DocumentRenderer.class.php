<?php

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;

abstract class DocumentRenderer implements IRenderer
{
    private $module;

    private $factory;

    abstract protected function doRender(Document $document);

    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->factory = new FieldRendererFactory($module);
    }

    public function render($payload)
    {
        if (! $payload instanceof Document)
        {
            throw new InvalidArgumentException(
                "Only the type Honeybee\Core\Dat0r\Document may be passed to this function!"
            );
        }

        return $this->doRender($payload);
    }

    protected function renderTwig(array $payload)
    {
        $loader = new Twig_Loader_Filesystem($this->getTemplateDirectory());
        $twig = new Twig_Environment($loader);

        return $twig->render($this->getTemplateName(), $payload);
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

    protected function isReadonly(Document $document)
    {
        $user = AgaviContext::getInstance()->getUser();
        $module = $document->getModule();
        $workflowStep = $document->getWorkflowTicket()->first()->getWorkflowStep();

        $writeAction = sprintf('%s.%s::write', $module->getOption('prefix'), $workflowStep);
        $createAction = sprintf('%s::create', $module->getOption('prefix'));

        $shortId = $document->getShortId();
        $requiredCredential = empty($shortId) ? $createAction : $writeAction;

        return ! $user->isAllowed($document, $requiredCredential);
    }
}
