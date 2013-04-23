<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\DocumentCollection;
use Dat0r\Core\Runtime\Field\ReferenceField;
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

        if ($parameters->hasParameter('referenceField') && $parameters->hasParameter('referenceModule'))
        {
            $fieldname = $parameters->getParameter('referenceField');
            $moduleClass = sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $parameters->getParameter('referenceModule'));
            $referenceModule = $moduleClass::getInstance();
            $referenceField = $referenceModule->getField($fieldname);

            $this->setAttribute('referenceModule', $referenceModule);
            $this->setAttribute('referenceField', $referenceField);
        }

        $service = $module->getService();

        $listConfig = ListConfig::create($this->buildListConfig());
        $listState = $parameters->getParameter('state');

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

        $data = $service->fetchListData($listConfig, $listState);

        $listState->setTotalCount($data['totalCount']);

        $listState->setData(
            $this->prepareListData($data['documents'])
        );

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

    protected function buildListConfig()
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
                if (get_class($this->getModule()) === $reference[ReferenceField::OPT_MODULE])
                {
                    $listSettings['clientSideController']['options']['reference_field'] = $referenceField->getName();
                    $listSettings['clientSideController']['options']['reference_module'] = $referenceModule->getName();
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

            $referenceFields = $this->getModule()->getFields(array(), array('Dat0r\Core\Runtime\Field\ReferenceField'));
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
            $workflowStep = $document->getWorkflowTicket()->getWorkflowStep();
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
                        'prompt' => $promptMsg
                    );
                }
            }

            $isInteractive = $workflowManager->isInInteractiveState($document);
            // will be passed to the ListItemViewModel.js and is the data available inside all the
            // batch callbacks and item actions invoked upon an ListController.js
            $documentListItemData = array(
                'data' => $document->toArray(),
                'workflow' => array('gates' => $gates, 'interactive' => $isInteractive)
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

                    $customActions[] = array(
                        'label' => $tm->_($actionName, $translationDomain),
                        'name' => $actionName,
                        'prompt' => ($promptMsg === $promptLangKey) ? FALSE : $promptMsg,
                        'binding' => array(
                            'method' => $actionDefinition['action'],
                            'parameters' => isset($actionDefinition['parameters']) ? $actionDefinition['parameters'] : array()
                        ) 
                    );
                }
                
                $documentListItemData['custom_actions'] = $customActions;
            }

            $data[] = $documentListItemData;
        }

        return $data;
    }

    protected function buildReferenceBatchConfig()
    {   
        $routing = $this->getContext()->getRouting();
        $tm = $this->getContext()->getTranslationManager();

        $referenceFields = $this->getModule()->getFields(array(), array('Dat0r\Core\Runtime\Field\ReferenceField'));
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
}
