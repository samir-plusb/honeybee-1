<?php

/**
 * The Shofi_Categories_SuggestAction class is responseable for delivering shofi category suggests (autocomplete).
 *
 * @version         $Id: Shofi_Categories_SuggestAction.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 * @subpackage      Mvc
 */
class Shofi_Categories_SuggestAction extends ShofiCategoriesBaseAction
{
    /**
     * The alias of the default field used to sort our list data.
     */
    const DEFAULT_SORT_FIELD = 'name';

    /**
     * The default direction used to sort our list data.
     */
    const DEFAULT_SORT_DIRECTION = 'asc';

    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $listConfig = ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        );
        $listState = ListState::fromArray(array(
            'offset' => $parameters->getParameter('offset', 0),
            'limit' => $parameters->getParameter('limit', $listConfig->getDefaultLimit()),
            'sortDirection' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'sortField' => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD),
            'search' => $parameters->getParameter('search_phrase'),
            'searchMode' => IListState::MODE_SUGGEST
        ));

        $finder = ShofiCategoriesFinder::create($listConfig);
        $result = $finder->find($listState);
        $listState->setTotalCount($result->getTotalCount());
        $listState->setData(
            $this->prepareSuggestData(
                $result->getItems(), 
                $finder->getWorkflowService()
            )
        );
        $listState->freeze();

        $this->setAttribute('config', $listConfig);
        $this->setAttribute('state', $listState);
        $this->setAttribute('user', $this->getContext()->getUser()->getAttribute('login'));

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
        var_dump($this->getContainer()->getValidationManager()->getErrorMessages());exit;

        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[]= $errMsg['message'];
        }
        $this->setAttribute('error_messages', $errors);
        return 'Error';
    }

    protected function prepareSuggestData(array $items, IWorkflowService $workflowService)
    {
        $suggestData = array();
        /* @var $workflowItem IWorkflowItem */
        foreach ($items as $workflowItem)
        {
            $masterRecord = $workflowItem->getMasterRecord();
            
            $suggestData[] = array(
                'name' => $masterRecord->getName(),
                'identifier' => $workflowItem->getIdentifier()
            );
        }
        return $suggestData;
    }
}

?>