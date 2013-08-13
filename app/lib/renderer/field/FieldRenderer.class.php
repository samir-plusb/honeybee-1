<?php

use Dat0r\Core\Field\IField;
use Dat0r\Core\Document\IDocument;
use Dat0r\Core\Module\AggregateModule;

abstract class FieldRenderer implements IRenderer
{
    private $field;

    protected $options;

    abstract protected function doRender(IDocument $document);

    abstract protected function getTemplateDirectory();

    abstract protected function getTemplateName();

    public function __construct(IField $field, array $options = array())
    {
        $this->field = $field;
        $this->options = $options;
    }

    public function render($payload)
    {
        if (! $payload instanceof IDocument)
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

    public function getField()
    {
        return $this->field;
    }

    protected function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    protected function getTranslationDomain(IDocument $document)
    {
        return $document->getModule()->getOption('prefix') . '.rendering';
    }

    protected function getRouteLink($name, array $parameters = array())
    {
        return AgaviContext::getInstance()->getRouting()->gen($name, $parameters);
    }

    protected function getTemplate()
    {
        $baseDir = $this->getTemplateDirectory();
        $templateName = $this->getTemplateName();

        return $baseDir . $templateName;
    }

    protected function isReadonly(IDocument $document)
    {
        $user = AgaviContext::getInstance()->getUser();
        $module = $document->getModule();

        if ($module instanceof AggregateModule)
        {
            return false;
        }

        $workflowStep = $document->getWorkflowTicket()->first()->getWorkflowStep();

        $writeAction = sprintf('%s.%s::write', $module->getOption('prefix'), $workflowStep);
        $createAction = sprintf('%s::create', $module->getOption('prefix'));

        $shortId = $document->getShortId();
        $requiredCredential = empty($shortId) ? $createAction : $writeAction;

        return ! $user->isAllowed($document, $requiredCredential)
            || isset($this->options['readonly']) && $this->options['readonly'] === TRUE;
    }
}
