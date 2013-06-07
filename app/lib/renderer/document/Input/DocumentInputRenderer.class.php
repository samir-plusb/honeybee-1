<?php

use Honeybee\Core\Dat0r\Document;

class DocumentInputRenderer extends DocumentRenderer
{
    static protected $standardTabs = array(
        'meta' => array(
            'is_default' => FALSE,
            'rows' => array(
                array(
                    'groups' => array(
                        'identity' => array('identifier', 'uuid', 'language', 'version', 'shortId', 'revision')
                    )
                )
            )
        )
    );

    public function getTranslationDomain()
    {
        return parent::getTranslationDomain() . '.input.document';
    }

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
        return array(
            'tabs' => $this->renderTabs($document),
            'tm' => $this->getTranslationManager(),
            'td' => $this->getTranslationDomain(),
            'ro' => AgaviContext::getInstance()->getRouting(),
            'modulePrefix' => $document->getModule()->getOption('prefix'),
            'controllerOptions' => htmlspecialchars(json_encode($this->getControllerOptions($document))),
            'editLink' => $this->getRouteLink('workflow.run'),
            'listLink' => $this->getRouteLink('list'),
            'readonly' => $this->isReadonly($document)
        );
    }

    protected function renderTabs(Document $document)
    {
        $renderedTabs = array();
        $defaultTab = NULL;
        $templateName = sprintf('%s.input_template',
            $this->getModule()->getOption('prefix')
        );
        $inputTpl = AgaviConfig::get($templateName, array());

        if (! isset($inputTpl['tabs']))
        {
            $inputTpl = $this->buildDefaultTemplate();
        }
        else
        {
            $inputTpl = $inputTpl['tabs'];
        }

        foreach ($inputTpl as $tabName => $tabDef)
        {
            if (isset($tabDef['is_default']) && TRUE === $tabDef['is_default'])
            {
                $defaultTab = $name;
            }
        }

        if (! $defaultTab && empty($inputTpl))
        {
            $defaultTab =  'meta';
        }
        else if(! empty($inputTpl))
        {
            $defaultTab =  'content';
        }

        $tplTabs = array_merge(self::$standardTabs, $inputTpl);
        $tplTabs[$defaultTab]['is_default'] = TRUE;

        foreach ($tplTabs as $tabName => $tabDeclaration)
        {
            $renderedRows = array();
            foreach ($tabDeclaration['rows'] as $row)
            {
                $renderedRows[] = $this->renderGroups($document, $row['groups']);
            }
            $renderedTabs[$tabName] = array(
                'rows' => $renderedRows,
                'is_default' => ($defaultTab === $tabName)
            );
        }

        return $renderedTabs;
    }

    protected function renderGroups(Document $document, array $groups)
    {
        $renderedGroups = array();

        foreach ($groups as $groupName => $fields)
        {
            $parts = explode(':', $groupName);
            $name = $parts[0];
            $renderedGroups[$name] = array(
                'width' => (2 === count($parts)) ? $parts[1] : 6,
                'fields' => $this->renderFields($document, $fields)
            );
        }

        return $renderedGroups;
    }

    protected function renderFields(Document $document, array $fields)
    {
        $renderedFields = array();

        //@todo verify that all affected fields may be rendered for the current context (user, module, intent etc.)
        foreach ($this->getModule()->getFields($fields) as $field)
        {
            $renderer = $this->getFactory()->createRenderer($field, FieldRendererFactory::CTX_INPUT);
            $renderedFields[$field->getName()] = $renderer->render($document);
        }

        return $renderedFields;
    }

    protected function getTemplate(Document $document = NULL)
    {
        $baseDir = $this->getTemplateDirectory();
        $templateName = $this->getTemplateName();

        return $baseDir . $templateName;
    }

    protected function getTemplateName()
    {
        return "Default.tpl.twig";
    }

    protected function getTemplateDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }

    protected function buildDefaultTemplate()
    {
        $excludeFields = array('identifier', 'uuid', 'language', 'version', 'shortId', 'revision');
        $mainGroup = array();

        foreach ($this->getModule()->getFields() as $field)
        {
            if (! in_array($field->getName(), $excludeFields))
            {
                $mainGroup[] = $field->getName();
            }
        }

        $groups = array();
        if (! empty($mainGroup))
        {
            $groups[] = $mainGroup;
        }

        return empty($groups) 
            ? array() 
            : array('content' => array(
                'is_default' => FALSE,
                'rows' => array(
                    array(
                        'groups' => array(
                            'main' => $mainGroup
                        )
                    )
                )
            ));
    }

    protected function getControllerOptions(Document $document)
    {

        return array(
            'autobind' => TRUE, 
            'identifier' => $document->getIdentifier(),
            'shortId' => $document->getShortId(),
            'revision' => $document->getRevision(),
            'uuid' => $document->getValue('uuid'),
            'language' => $document->getValue('language'),
            'version' => $document->getValue('version'),
            'readonly' => $this->isReadonly($document)
        );
    }
}
