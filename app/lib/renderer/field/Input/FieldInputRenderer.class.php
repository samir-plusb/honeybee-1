<?php

use Honeybee\Core\Dat0r\Document;

class FieldInputRenderer extends FieldRenderer
{
    protected function doRender(Document $document)
    {
        $payload = $this->getPayload($document);

        if (preg_match('/\.twig$/', $this->getTemplateName()))
        {
            return $this->renderTwig($payload);
        }
        else
        {
            extract($payload);
            ob_start();

            include $this->getTemplate();

            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }
    }

    protected function getPayload(Document $document)
    {
        $tm = $this->getTranslationManager();
        $td = $this->getTranslationDomain($document);

        $fieldName = $this->getField()->getName();
        $widgetType = $this->getWidgetType($document);

        return array( 
            'fieldName' => $fieldName,
            'field' => $this->getField(),
            'inputName' => $this->generateInputName($document),
            'fieldId' => 'input-' . $fieldName,
            'placeholder' => $this->getField()->getOption('placesholder', ''),
            'fieldValue' => $this->renderFieldValue($document),
            'widgetType' => $widgetType,
            'hasWidget' => ($widgetType !== NULL),
            'widgetOptions' => $this->renderWidgetOptions($document),
            'readonly' => $this->isReadonly($document),
            'tm' => $tm,
            'td' => $td
        );
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
        $value = '';
        if (isset($this->options['fieldpath']))
        {
            $fieldParts = explode('.', $this->options['fieldpath']);
            $module = $document->getModule();
            $curDoc = $document;

            while (count($fieldParts) > 1)
            {
                $fieldName = array_shift($fieldParts);
                $curDoc = $curDoc->getValue($fieldName);
            }
            $value = $curDoc->getValue($fieldParts[0]);
        }
        else
        {
            $value = $document->getValue($this->getField()->getName());
        }

        return is_scalar($value) ? $value : '';
    }

    protected function getWidgetOptions(Document $document)
    {
        $prefix = $document->getModule()->getOption('prefix');
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldname = $this->getField()->getName();
        $widgetOptions = array();
        
        if (isset($widgetSettings[$fieldname]))
        {
            $widgetDef = $widgetSettings[$fieldname];
            $widgetOptions = $widgetDef['options'];
        }

        $widgetOptions['readonly'] = $this->isReadonly($document); 
        $widgetOptions['autobind'] = TRUE;

        return $widgetOptions;
    }

    protected function generateInputName(Document $document)
    {
        $name = '';

        if (isset($this->options['fieldpath']))
        {
            $first = true;
            foreach(explode('.', $this->options['fieldpath']) as $part)
            {
                if ($first)
                {
                    $name = sprintf(
                        '%s[%s]', 
                        $document->getModule()->getOption('prefix'),
                        $part
                    );
                    $first = FALSE;
                }
                else
                {
                    $name .= sprintf('[%s]', $part);
                }
            }
        }
        else
        {
            $name = sprintf(
                '%s[%s]', 
                $document->getModule()->getOption('prefix'),
                $this->getField()->getName()
            );
        }

        return $name;
    }

    protected function getTemplateName()
    {
        return "Default.tpl.twig";
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
