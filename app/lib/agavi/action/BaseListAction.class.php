<?php

/**
 * The BaseListAction class serves as a base class to all actions that slot the Common/ListAction.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Agavi
 * @subpackage      Action
 */
class BaseListAction extends ProjectBaseAction
{
    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $module = $this->getModule();
        $service = $module->getService();

        $listConfig = ListConfig::create($this->buildListConfig());
        $listState = $parameters->getParameter('state');

        $data = $service->fetchListData($listConfig, $listState);

        $listState->setTotalCount($data['totalCount']);
        $listState->setData(
            $this->prepareListData($data['documents'])
        );

        $this->setAttribute('config', $listConfig);
        $this->setAttribute('state', $listState);
        $this->setAttribute('module', $module);

        return 'Success';
    }

    /**
     * Handles validation errors that occur for any our derivates.
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return string The name of the view to invoke.
     */
    public function handleReadError(AgaviRequestDataHolder $parameters)
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
        $settingsKey = $this->buildListConfigKey();
        $listSettings = AgaviConfig::get($settingsKey, array());
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
        $routing = $this->getContext()->getRouting();

        return $listSettings;
    }

    protected function buildListConfigKey()
    {
        return sprintf(
            '%s.list_config', 
            $this->getModule()->getOption('prefix')
        );
    }

    protected function prepareListData(HoneybeeDocumentCollection $documents)
    {
        $data = array();

        foreach ($documents as $document)
        {
            $data[] = array(
                'data' => $document->toArray(),
                'ticket' => array()
            );
        }

        return $data;
    }
}
