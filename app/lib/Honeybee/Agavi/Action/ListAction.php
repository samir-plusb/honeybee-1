<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\DocumentCollection;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\RelationManager;
use Dat0r\Core\Field\ReferenceField;
use Dat0r\Core\Field\AggregateField;
use Honeybee\Core\Import;
use ListConfig;

/**
 * The BaseListAction class serves as a base class to all actions that slot the Common/ListAction.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ListAction extends BaseAction
{
    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(\AgaviRequestDataHolder $parameters)
    {
        $module = $this->getModule();
        $listState = $parameters->getParameter('state');

        if (($fieldname = $listState->getReferenceField()) && ($referenceModuleName = $listState->getReferenceModule()))
        {
            $fieldparts = explode('.', $fieldname);
            $moduleClass = sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $referenceModuleName);
            $referenceModule = $moduleClass::getInstance();
            $curModule = $referenceModule;
            $curField = $curModule->getField(array_shift($fieldparts));
            while (count($fieldparts) > 0)
            {
                if ($curField instanceof AggregateField)
                {
                    $aggregateModuleName = array_shift($fieldparts);
                    foreach ($curField->getAggregateModules() as $aggregateModule)
                    {
                        if ($aggregateModule->getName() === $aggregateModuleName)
                        {
                            $curModule = $aggregateModule;
                            break;
                        }
                    }
                }
                $curField = $curModule->getField(array_shift($fieldparts));
            }
            $this->setAttribute('referenceModule', $curModule);
            $this->setAttribute('referenceField', $curField);
            $this->setAttribute('referenceFieldId', $listState->getReferenceFieldId());
        }

        $service = $module->getService();

        $listConfig = ListConfig::create($this->buildListConfig($parameters));

        $this->setAttribute('config', $listConfig);
        $this->setAttribute('state', $listState);
        $this->setAttribute('module', $module);

        // apply default limit of the module if none is set on the liststate
        if (!$listState->hasLimit())
        {
            $listState->setLimit($listConfig->getDefaultLimit());
        }

        // apply default offset if none is set on the liststate
        if (!$listState->hasOffset())
        {
            $listState->setOffset(0);
        }

        if ('xml_zipped' === $parameters->getParameter('export_format'))
        {
            $outputType = $this->getContext()->getController()->getOutputType('zip');
            $this->getContainer()->setOutputType($outputType);
            $this->setAttribute('zip_file', $this->createXmlZipArchive());
        }
        else if ('csv' === $parameters->getParameter('export_format'))
        {
            $outputType = $this->getContext()->getController()->getOutputType('csv');
            $this->getContainer()->setOutputType($outputType);
        }
        else if ('xml' === $parameters->getParameter('export_format'))
        {
            $outputType = $this->getContext()->getController()->getOutputType('xml');
            $this->getContainer()->setOutputType($outputType);
        }
        else
        {
            RelationManager::setMaxRecursionDepth(0);
            RelationManager::startPooling();
            $data = $service->fetchListData($listConfig, $listState);
            RelationManager::stopPooling();
            $listState->setTotalCount($data['totalCount']);
            $listState->setData($this->prepareListData($data['documents']));
        }

        return 'Success';
    }

    /**
     * Handles validation errors that occur for any our derivates.
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return string The name of the view to invoke.
     */
    public function handleReadError(\AgaviRequestDataHolder $parameters)
    {
        $this->setAttribute('module', $this->getModule());

        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[implode(', ', array_values($errMsg['errors']))] = $errMsg['message'];
        }

        $this->setAttribute('error_messages', $errors);

        return 'Error';
    }

    public function getCredentials()
    {
        return sprintf(
            '%s::read',
            $this->getModule()->getOption('prefix')
        );
    }

    protected function buildListConfig(\AgaviRequestDataHolder $parameters)
    {
        $settingsKey = sprintf('%s.list_config', $this->getModule()->getOption('prefix'));
        $listSettings = \AgaviConfig::get($settingsKey, array());
        $fields = array_values($this->getModule()->getFields()->toArray());

        if (! isset($listSettings['fields']))
        {
            $listFields = array();

            for($i = 0; $i < 5 && $i < count($fields); $i++)
            {
                $field = $fields[$i];
                $listFields[$field->getName()] = array(
                    'name' => $field->getName(),
                    'valuefield' => $field->getName(),
                    'sortfield' => sprintf('%s.raw', $field->getName())
                );
            }
            $listSettings['fields'] = $listFields;
        }

        if (! isset($listSettings['suggestField']) && isset($fields[0]))
        {
            $listSettings['suggestField'] = $fields[0]->getName();
        }
        $translationManager = $this->getContext()->getTranslationManager();
        $routing = $this->getContext()->getRouting();

        $listSettings['hasTreeView'] = $this->getModule()->isActingAsTree();
        $listSettings['clientSideController']['options']['module'] = $this->getModule()->getOption('prefix');
        $listSettings['clientSideController']['options']['event_origin'] = $routing->getBaseHref();
        $listSettings['clientSideController']['options']['reference_batches'] = $this->buildReferenceBatchConfig();

        if ($this->hasAttribute('referenceField'))
        {
            $referenceField = $this->getAttribute('referenceField');
            $referenceModule = $this->getAttribute('referenceModule');

            foreach ($referenceField->getOption(ReferenceField::OPT_REFERENCES) as $reference)
            {
                $referenceModuleName = $reference[ReferenceField::OPT_MODULE];
                if ($referenceModuleName{0} !== '\\')
                {
                    $referenceModuleName = '\\' . $referenceModuleName;
                }
                if ('\\' . get_class($this->getModule()) === $referenceModuleName)
                {
                    $listSettings['clientSideController']['options']['reference_field'] = $referenceField->getName();
                    $listSettings['clientSideController']['options']['reference_module'] = $referenceModule->getName();
                    $listSettings['clientSideController']['options']['reference_field_id'] = $this->getAttribute('referenceFieldId');
                    $listSettings['clientSideController']['options']['reference_settings'] = array(
                        'identity_field' => $reference[ReferenceField::OPT_IDENTITY_FIELD],
                        'display_field' => $reference[ReferenceField::OPT_DISPLAY_FIELD]
                    );
                    break;
                }
            }
        }
        $enableFoldersSetting = sprintf('%s.sidebar.folders.enabled', $this->getModule()->getOption('prefix'));

        if (! $this->hasAttribute('referenceField') && TRUE === \AgaviConfig::get($enableFoldersSetting, FALSE))
        {
            $listSettings['itemActions'] = isset($listSettings['itemActions']) ? $listSettings['itemActions'] : array();
            // reference-fields that are affected by a configured 'assignReference' item action.
            $itemActionReferenceFields = array();
            foreach ($listSettings['itemActions'] as $actionName => $itemAction)
            {
                if ('assignReference' === $itemAction['action'])
                {
                    $itemActionReferenceFields[$itemAction['parameters'][0]] = $actionName;
                }
            }

            $referenceFields = $this->getModule()->getFields(array(), array('Dat0r\Core\Field\ReferenceField'));
            // all targets available to the list for enabling/disabling tree-modules within the sidebar.
            $sidebarTreeTargets = array();
            foreach ($referenceFields as $referenceField)
            {
                foreach ($referenceField->getReferencedModules() as $referencedModule)
                {
                    $isFieldBoundToItemAction = isset($itemActionReferenceFields[$referenceField->getName()]);
                    if ($isFieldBoundToItemAction && $referencedModule->isActingAsTree())
                    {
                        $modulePrefix = $this->getModule()->getOption('prefix');
                        $sidebarTreeTargets[] = array(
                            'module' => $referencedModule->getOption('prefix'),
                            'related_action' => $itemActionReferenceFields[$referenceField->getName()],
                            'labels' => array(
                                'assign' => $translationManager->_(
                                    'assign_' . $referenceField->getName(),
                                    $modulePrefix . '.list'
                                ),
                                'abort' => $translationManager->_(
                                    'abort_' . $referenceField->getName() . '_assignment', 
                                    $modulePrefix . '.list'
                                )
                            )
                        );
                    }
                }
            }

            $listSettings['sidebarTreeTargets'] = $sidebarTreeTargets;
            $batchActions = array();
            $coreWorkflowActions = array('promote', 'demote', 'delete');
            foreach ($listSettings['batchActions'] as $actionName => $cfg)
            {
                if (in_array($actionName, $coreWorkflowActions))
                {
                    $batchActions[$actionName] = $cfg;
                    continue;
                }

                $aclAction = $this->getModule()->getOption('prefix') . '::' . $actionName;
                if ($this->getContext()->getUser()->isAllowed($this->getModule(), $aclAction))
                {
                    $batchActions[$actionName] = $cfg;
                }
            }
            $listSettings['batchActions'] = $batchActions;
        }

        return $listSettings;
    }

    protected function prepareListData(DocumentCollection $documents)
    {
        $data = array();
        $module = $this->getModule();
        $tm = $this->getContext()->getTranslationManager();
        $translationDomain = sprintf('%s.list', $module->getOption('prefix'));
        // this guy knows everything about the current workflow state and where we can go from here.
        $workflowManager = $module->getWorkflowManager();

        foreach ($documents as $document)
        {
            $gates = array();
            $workflowStep = $document->getWorkflowTicket()->first()->getWorkflowStep();
            // iterate over all the gates of the current workflow step
            // and check if the current user may access them.
            foreach ($workflowManager->getPossibleGates($document) as $gateName)
            {
                // magic! there is a convention for the translations of a module's list domain,
                // that allows you to trigger confirm prompts in the GUI and show a specific translated message,
                // when a user wants to execute the corresponding action.
                // :Example:      <ae:parameter name="edit.promote.prompt">Really promote?</ae:parameter>
                // :Explaination: show a prompt with "Really promote?",
                //                when the user attempts to promote a document that is in edit state.
                $promptLangKey = sprintf('%s.%s.prompt', $workflowStep, $gateName);
                $promptMsg = $tm->_($promptLangKey, $translationDomain);
                if ($promptMsg === $promptLangKey)
                {
                    $promptMsg = FALSE;
                }
                // build the resource action-key by convention ...
                $action = $module->getOption('prefix') . '.' . $workflowStep.'::'. $gateName;
                $user = $this->getContext()->getUser();
                // ... and check if the user has access (via zend-acl).
                if ($user->isAllowed($document, $action))
                {
                    $gates[] = array(
                        'label' => $tm->_($workflowStep.'.'.$gateName, $translationDomain),
                        'name' => $gateName,
                        'key' => $workflowStep.'.'.$gateName,
                        'prompt' => $promptMsg
                    );
                }
            }

            $isInteractive = $workflowManager->isInInteractiveState($document);
            $mayRead = $user->isAllowed($document, sprintf('%s.%s::read', $module->getOption('prefix'), $workflowStep));
            $mayWrite = $user->isAllowed($document, sprintf('%s.%s::write', $module->getOption('prefix'), $workflowStep));
            // will be passed to the ListItemViewModel.js and is the data available inside all the
            // batch callbacks and item actions invoked upon an ListController.js
            $documentListItemData = array(
                'data' => $document->getValues(),
                'workflow' => array('gates' => $gates, 'interactive' => ($isInteractive && $mayRead), 'readonly' => !$mayWrite)
            );
            // for interactive workflow states we support custom item actions.
            // they are appended to the default system actions.
            if ($isInteractive)
            {
                // @todo check if the current has the permission to execute writes within the current state.
                $customActions = array();

                foreach ($this->getAttribute('config')->getItemActions() as $actionName => $actionDefinition)
                {
                    // @todo individual permission for custom actions
                    // or is it enough to just check write access for the current state?
                    $promptLangKey = sprintf('%s.%s.prompt', $workflowStep, $actionName);
                    $promptMsg = $tm->_($promptLangKey, $translationDomain);
                    $aclAction = sprintf('%s::%s', $module->getOption('prefix'), $actionName);
                    if ($mayWrite && $user->isAllowed($document, $aclAction))
                    {
                        $customActions[] = array(
                            'label' => $tm->_($actionName, $translationDomain),
                            'name' => $actionName,
                            'key' => $workflowStep . '.' . $actionName,
                            'prompt' => ($promptMsg === $promptLangKey) ? FALSE : $promptMsg,
                            'binding' => array(
                                'method' => $actionDefinition['action'],
                                'parameters' => isset($actionDefinition['parameters']) ? $actionDefinition['parameters'] : array()
                            )
                        );
                    }
                }

                $documentListItemData['custom_actions'] = $customActions;
            }
            else
            {
                $documentListItemData['custom_actions'] = array();
            }
            $documentListItemData['css_classes'] = array('state-'.$workflowStep);
            $data[] = $documentListItemData;
        }

        return $data;
    }

    protected function buildReferenceBatchConfig()
    {
        $routing = $this->getContext()->getRouting();
        $tm = $this->getContext()->getTranslationManager();

        $referenceFields = $this->getModule()->getFields(array(), array('Dat0r\Core\Field\ReferenceField'));
        $referenceBatchConfigs = array();

        foreach ($referenceFields as $referenceField)
        {
            $maxCount = (int)$referenceField->getOption(ReferenceField::OPT_MAX_REFERENCES, 0);
            $updateUrl = urldecode(htmlspecialchars_decode(
                $routing->gen(
                    sprintf('%s.workflow.execute', $this->getModule()->getOption('prefix')),
                    array('id' => '{ID}')
                )
            ));

            $translationDomain = sprintf('%s.list', $this->getModule()->getOption('prefix'));
            $refWidgetOptions = array(
                'autobind' => TRUE,
                'event_origin' => $routing->getBaseHref(),
                'autocomplete' => TRUE,
                'autocomp_mappings' => $this->buildReferenceWidgetSuggestOptions($referenceField),
                'fieldname' => $referenceField->getName(),
                'field_id' => 'reference-batch-' . $referenceField->getName(),
                'realname' => $referenceField->getName(),
                'max' => $maxCount,
                'disable_backdrop' => TRUE,
                'tags' => array(),
                'tpl' => 'Stacked',
                'texts' =>  array(
                    'placeholder' => $tm->_('assign_references', $translationDomain),
                    'searching' => $tm->_('searching', $translationDomain),
                    'too_short' => $tm->_('reference_suggest_to_short', $translationDomain),
                    'too_long' => $tm->_('max_references_reached', $translationDomain),
                    'no_results' => $tm->_('no_references_found', $translationDomain),
                    'field_label' => $tm->_($referenceField->getName(), $translationDomain),
                    'override_references' => $tm->_('override_references', $translationDomain),
                    'append_references' => $tm->_('append_reference', $translationDomain),
                    'assign_references' => $tm->_('assign_references', $translationDomain)
                )
            );
            $referenceBatchConfigs[$referenceField->getName()] = array(
                'widget_options' => $refWidgetOptions,
                'update_url' => $updateUrl
            );
        }

        return $referenceBatchConfigs;
    }

    protected function buildReferenceWidgetSuggestOptions(ReferenceField $referenceField)
    {
        $routing = $this->getContext()->getRouting();
        $tm = $this->getContext()->getTranslationManager();
        $references = $referenceField->getOption(ReferenceField::OPT_REFERENCES);

        $autoCompleteMappings = array();
        foreach ($references as $reference)
        {
            $referenceModuleClass = $reference['module'];
            $displayField = $reference[ReferenceField::OPT_DISPLAY_FIELD];
            $identityField = $reference[ReferenceField::OPT_IDENTITY_FIELD];
            $referencedModule = $referenceModuleClass::getInstance();
            $modulePrefix = $referencedModule->getOption('prefix');
            $suggestRouteName = sprintf('%s.suggest', $modulePrefix);
            $listRouteName = sprintf('%s.list', $modulePrefix);

            $autoCompleteMappings[$modulePrefix] = array(
                'display_field' => $displayField,
                'identity_field' => $identityField,
                'module_label' => $tm->_($referencedModule->getName(), 'modules.labels'),
                'list_url' => htmlspecialchars_decode(
                    urldecode($routing->gen($listRouteName, array(
                        'referenceModule' => $this->getModule()->getName(),
                        'referenceField' => $referenceField->getName()
                    )
                ))),
                'uri' => htmlspecialchars_decode(
                    urldecode($routing->gen($suggestRouteName, array(
                        'term' => '{PHRASE}',
                        'display_field' => $displayField,
                        'identity_field' => $identityField
                    )))
                )
            );
        }

        return $autoCompleteMappings;
    }

    protected function createXmlZipArchive()
    {
        $search_spec = array();
        $list_state = $this->getAttribute('state');
        if ($list_state->hasSearch()) {
            $search_spec['search'] = $list_state->getSearch();
        }
        if ($list_state->hasFilter()) {
            $search_spec['filter'] = $list_state->getFilter();
        }

        $document_service = $this->getModule()->getService();
        $export = $this->getModule()->getService('export')->getExport('list-xml-zip');
        $document_service->walkDocuments($search_spec, 100, function($document) use ($export)
        {
            $export->publish($document);
        });
        $export->getStorage()->getResource()->close();

        return $export->getStorage()->getResource();
    }
}
