<?php

/**
 * The Shofi_ListAction class is responseable for delivering shofi places to consumers.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_ListAction extends ShofiBaseAction
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
        $listConfig = ListConfig::fromArray($this->prepareListConfig());
        $listState = ListState::fromArray(array(
            'offset' => $parameters->getParameter('offset', 0),
            'limit' => $parameters->getParameter('limit', $listConfig->getDefaultLimit()),
            'sortDirection' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'sortField' => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD),
            'search' => $parameters->getParameter('search_phrase'),
            'filter' => $parameters->getParameter('filter', array())
        ));
        $shofiFinder = ShofiFinder::create($listConfig);
        $result = $shofiFinder->find($listState);

        $listState->setTotalCount($result->getTotalCount());
        $listState->setData(
            $this->prepareListData(
                $result->getItems(),
                $shofiFinder->getWorkflowService()
            )
        );
        $listState->freeze();

        $this->setAttribute('config', $listConfig);
        $this->setAttribute('state', $listState);

        $routing = $this->getContext()->getRouting();
        $this->setAttribute('category_autocomplete', json_encode(array(
            'autobind' => TRUE,
            'autocomplete_uri' => urldecode(htmlspecialchars($routing->gen('shofi_categories.suggest', array('search_phrase' => '{PHRASE}')))),
            'autocomplete_prop' => 'name',
            'autocomplete_value_prop' => 'identifier',
            'autocomplete_limit' => 50,
            'fieldname' => 'filter[detailItem.category]'
        )));
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
    public function handleError(AgaviRequestDataHolder $parameters)
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[implode(', ', array_values($errMsg['errors']))] = $errMsg['message'];
        }
        $this->setAttribute('error_messages', $errors);
        return 'Error';
    }

    protected function prepareListConfig()
    {
        $routing = $this->getContext()->getRouting();
        $config = AgaviConfig::get('shofi.list_config');
        $options = $config['clientSideController']['options'];
        $categoryAutoCompUrl = AgaviToolkit::expandDirectives($options['category_batch']['autocomplete_url']);
        $options['category_batch'] = array_merge(
            $options['category_batch'],
            array(
                'autocomplete_url' => (FALSE === strpos($categoryAutoCompUrl, 'http')) ? urldecode(htmlspecialchars_decode($routing->gen(
                    $categoryAutoCompUrl,
                    array('search_phrase' => '{PHRASE}')
                ))) : $categoryAutoCompUrl,
                'post_url' => urldecode(htmlspecialchars_decode(
                    $routing->gen('workflow.run', array('type' => 'shofi', 'ticket' => '{TICKET}', '_page' => 'DetailItem')
                )))
            )
        );
        $config['clientSideController']['options'] = $options;
        return $config;
    }

    protected function prepareListData(array $items, IWorkflowService $workflowService)
    {
        $listData = array();
        $ticketStore = $workflowService->getWorkflowSupervisor()->getWorkflowTicketStore();
        /* @var $workflowItem IWorkflowItem */
        foreach ($items as $workflowItem)
        {
            $cssClasses = array();
            $workflowItemData = $workflowItem->toArray();
            // @todo This findOne query is a potential bottle neck and does not scale!
            // Better: Use the read connection instead of the write connection here.
            // Even Better: Collect all tickets id's and fetch the data in one query.
            $ticket = $ticketStore->fetchByIdentifier(
                $workflowItem->getTicketId()
            );
            if (! $ticket)
            {
                error_log(__METHOD__ . " - Missing ticket for workflow item: " . $workflowItem->getIdentifier());
                continue;
            }
            $ticketData = array(
                'id' => $ticket->getIdentifier(),
                'rev' => $ticket->getRevision()
            );
            $listData[] = array(
                'data' => $workflowItemData,
                'ticket' => $ticketData,
                'css_classes' => $cssClasses
            );
        }
        return $listData;
    }
}

?>