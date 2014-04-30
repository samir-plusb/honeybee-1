<?php

use Honeybee\Core\Dat0r\Document;

class DocumentInputRenderer extends DocumentRenderer
{
    static protected $standardTabs = array(
        '_internal_meta' => array(
            'is_default' => FALSE,
            'visibility' => 'hidden',
            'rows' => array(
                array(
                    'groups' => array(
                        'identity' => array(
                            'identifier',
                            'uuid',
                            'language',
                            'version',
                            'shortId',
                            'revision',
                            'slug'
                        )
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
        $list_setting_name = sprintf('%s_last_list_url', $document->getModule()->getOption('prefix'));
        $last_list_url = AgaviContext::getInstance()->getUser()->getAttribute($list_setting_name, 'honeybee.list', false);

        return array(
            'tabs' => $this->renderTabs($document),
            'tm' => $this->getTranslationManager(),
            'td' => $this->getTranslationDomain(),
            'ro' => AgaviContext::getInstance()->getRouting(),
            'modulePrefix' => $document->getModule()->getOption('prefix'),
            'controllerOptions' => htmlspecialchars(json_encode($this->getControllerOptions($document))),
            'editLink' => $this->getRouteLink('workflow.run'),
            'listLink' => $last_list_url ? $last_list_url : $this->getRouteLink('list'),
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
            $defaultTab =  '_internal_meta';
        }
        else if(! empty($inputTpl))
        {
            $defaultTab =  'content';
        }

        $tplTabs = array_merge(self::$standardTabs, $inputTpl);
        $tplTabs[$defaultTab]['is_default'] = TRUE;

        foreach ($tplTabs as $tabName => &$tabDeclaration)
        {
            $renderedRows = array();
            if (!isset($tabDeclaration['visibility']))
            {
                $tabDeclaration['visibility'] = 'visible';
            }
            foreach ($tabDeclaration['rows'] as $row)
            {
                if (isset($row['type']) && $row['type'] === 'custom')
                {
                    $renderedRows[] = array(
                        'type' => 'custom',
                        'content' => $this->renderCustomRow($document, $row)
                    );
                }
                else
                {
                    $renderedRows[] = array(
                        'type' => 'default',
                        'groups' => $this->renderGroups($document, $row['groups'])
                    );
                }
            }
            $renderedTabs[$tabName] = array(
                'rows' => $renderedRows,
                'visibility' => $tabDeclaration['visibility'],
                'is_default' => ($defaultTab === $tabName)
            );
        }
        return $renderedTabs;
    }

    protected function renderCustomRow(Document $document, array $rowConfig)
    {
        $twig = new Twig_Environment(
            new Twig_Loader_Filesystem(dirname($rowConfig['template']))
        );

        return $twig->render(
            basename($rowConfig['template']),
            array()
        );
    }

    protected function renderGroups(Document $document, array $groups)
    {
        $renderedGroups = array();
        $prevGroup = null;
        foreach ($groups as $groupName => $fields)
        {
            $renderBox = true;
            $openColumn = true;
            $closeColumn = true;
            $parts = explode(':', $groupName);
            $name = $parts[0];
            if (strpos($name, '+') === 0) {
                $openColumn = false;
                if ($prevGroup) {
                    $renderedGroups[$prevGroup]['closeColumn'] = false;
                }
                $name = substr($name, 1);
            }
            if (strpos($name, '-') === 0) {
                $renderBox = false;
                $name = substr($name, 1);
            }
            $renderedGroups[$name] = array(
                'boxed' => $renderBox,
                'openColumn' => $openColumn,
                'closeColumn' => $closeColumn,
                'width' => (2 === count($parts)) ? $parts[1] : 6,
                'fields' => $this->renderFields($document, $fields)
            );
            $prevGroup = $name;
        }

        return $renderedGroups;
    }

    protected function renderFields(Document $document, array $fields)
    {
        $renderedFields = array();
        $fieldsToRender = array();
        //@todo verify that all affected fields
        // may be rendered for the current context (user, module, intent etc.)
        foreach ($fields as $fieldName)
        {
            $renderer = NULL;
            $fieldParts = explode('.', $fieldName);
            $options = array('fieldpath' => $fieldName);

            if (count($fieldParts) > 1)
            {
                $field = $this->getField($fieldParts);
                $renderer = $this->getFactory()->createRenderer($field, FieldRendererFactory::CTX_INPUT, $options);
                $renderedFields[$field->getName()] = $renderer->render($document);
            }
            else
            {
                $field = $this->getModule()->getField($fieldName);
                $renderer = $this->getFactory()->createRenderer($field, FieldRendererFactory::CTX_INPUT, $options);
                $renderedFields[$field->getName()] = $renderer->render($document);
            }
        }

        return $renderedFields;
    }

    protected function getField(array $fieldParts)
    {
        $module = $this->getModule();

        while (count($fieldParts) > 1)
        {
            $fieldName = array_shift($fieldParts);
            $field = $module->getField($fieldName);
            $modules = $field->getAggregateModules();
            $module = reset($modules);
        }
        return $module->getField($fieldParts[0]);
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
        $routing = AgaviContext::getInstance()->getRouting();
        $module_prefix = $document->getModule()->getOption('prefix');

        return array(
            'autobind' => TRUE,
            'identifier' => $document->getIdentifier(),
            'shortId' => $document->getShortId(),
            'revision' => $document->getRevision(),
            'uuid' => $document->getValue('uuid'),
            'language' => $document->getValue('language'),
            'version' => $document->getValue('version'),
            'readonly' => $this->isReadonly($document),
            'event_origin' => $routing->getBaseHref(),
            'unlock_url' => $routing->gen($module_prefix . '.checkout', array('id' => $document->getIdentifier()))
        );
    }
}
