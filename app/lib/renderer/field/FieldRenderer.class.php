<?php

use Dat0r\Core\Runtime\Field\IField;
use Honeybee\Core\Dat0r\Document;

abstract class FieldRenderer implements IRenderer
{
    private $field;

    protected $options;

    abstract protected function doRender(Document $document);

    abstract protected function getTemplateDirectory();

    abstract protected function getTemplateName();

    public function __construct(IField $field, array $options = array())
    {
        $this->field = $field;
        $this->options = $options;
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

    public function getField()
    {
        return $this->field;
    }

    protected function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    protected function getTranslationDomain(Document $document)
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
}
