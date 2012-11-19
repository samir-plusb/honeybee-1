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
        $listConfig = $this->createListConfig();
        $listState = $parameters->getParameter('state');

        $listData = $this->prepareListData(
            $this->fetchDocuments($listConfig, $listState)
        );

        $listState->setTotalCount($listData['totalCount']);
        $listState->setData($listData['data']);

        $this->setAttribute('config', $listConfig);
        $this->setAttribute('state', $listState);
        $this->setAttribute('module', $this->getModule());

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

    protected function fetchDocuments(IListConfig $config, IListState $state)
    {
        // @todo build query from list state and pass to the repo's find method.
        return $this->getModule()->getRepository()->read(array(
            'article-e9260f2f896732c15308e1df6d000e7a',
            'article-e9260f2f896732c15308e1df6d004a6e'
        ));
    }

    // @todo switch from collection to finder result,
    // thereby adding real 'totalCount' support.
    protected function prepareListData(HoneybeeDocumentCollection $documents)
    {
        $listData = array();

        foreach ($documents as $document)
        {
            $listData[] = array('data' => $document->toArray(), 'ticket' => array());
        }

        return array('data' => $listData, 'totalCount' => count($listData));
    }

    protected function createListConfig()
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

        return ListConfig::create($listSettings);
    }

    protected function buildListConfigKey()
    {
        return sprintf(
            '%s.list_config', 
            $this->getModule()->getOption('prefix')
        );
    }
}
