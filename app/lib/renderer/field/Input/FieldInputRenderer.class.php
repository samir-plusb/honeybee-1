<?php

use Honeybee\Core\Dat0r\Document;

class FieldInputRenderer extends FieldRenderer
{
    protected function doRender(Document $document)
    {
        $tm = $this->getTranslationManager();
        $td = $this->getTranslationDomain($document);
        $template = $this->getTemplate();
        $fieldName = $this->getField()->getName();
        $field = $this->getField();
        $inputName = $this->generateInputName($document);
        $fieldId = 'input-' . $fieldName;
        $placeholder = $this->getField()->getOption('placesholder', '');
        $fieldValue = $this->renderFieldValue($document);
        $widgetType = $this->getWidgetType($document);
        $hasWidget = ($widgetType !== NULL);
        $widgetOptions = $this->renderWidgetOptions($document);

        ob_start();

        include $template;

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function getWidgetType(Document $document)
    {
        $prefix = $document->getModule()->getOption('prefix');
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldname = $this->getField()->getName();
        $widget = NULL;

        if (isset($widgetSettings[$fieldname]))
        {
            $widgetDef = $widgetSettings[$fieldname];
            $widget = $widgetDef['type'];
        }

        return $widget;
    }

    protected function renderWidgetOptions(Document $document)
    {
        return htmlspecialchars(
            json_encode($this->getWidgetOptions($document))
        );
    }

    protected function renderFieldValue(Document $document)
    {
        $value = $document->getValue($this->getField()->getName());
        return is_scalar($value) ? $value : '';
    }

    protected function getWidgetOptions(Document $document)
    {
        $prefix = $document->getModule()->getOption('prefix');
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldname = $this->getField()->getName();
        $widgetOptions = NULL;
        
        if (isset($widgetSettings[$fieldname]))
        {
            $widgetDef = $widgetSettings[$fieldname];
            $widgetOptions = $widgetDef['options'];
        }

        return $widgetOptions;
    }

    protected function generateInputName(Document $document)
    {
        // @todo introduce a further structure level, let's call it 'groups'.
        // this would allow to render the same form multiple times without value collusions.
        return sprintf(
            '%s[%s]', 
            $document->getModule()->getOption('prefix'),
            $this->getField()->getName()
        );
    }

    protected function getTemplateName()
    {
        return "Default.tpl.php";
    }

    protected function getTemplateDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }

    public function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    public function getTranslationDomain(Document $document)
    {
        return parent::getTranslationDomain($document) . '.input.field';
    }
}
