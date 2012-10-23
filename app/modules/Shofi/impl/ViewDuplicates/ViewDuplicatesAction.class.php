<?php

/**
 * The ViewDuplicatesAction class is responseable for delivering shofi places to consumers.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_ViewDuplicatesAction extends ShofiBaseAction
{
    /**
     * The alias of the default field used to sort our list data.
     */
    const DEFAULT_SORT_FIELD = 'name';

    /**
     * The default direction used to sort our list data.
     */
    const DEFAULT_SORT_DIRECTION = 'asc';

    protected $shofiFinder;

    protected $categoryFinder;

    public function initialize(AgaviExecutionContainer $container)
    {
        parent::initialize($container);

        $this->categoryFinder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        $this->shofiFinder = ShofiFinder::create(
            ListConfig::fromArray($this->prepareListConfig())
        );
    }

    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $workflowService = ShofiCategoriesWorkflowService::getInstance();
        $category = $workflowService->fetchWorkflowItemById(
            $parameters->getParameter('category')
        );

        if (! $category)
        {
            return 'Error';
        }

        $listConfig = $this->shofiFinder->getListConfig();
        $listState = ListState::fromArray(array(
            'offset' => $parameters->getParameter('offset', 0),
            'limit' => $parameters->getParameter('limit', $listConfig->getDefaultLimit()),
            'sortDirection' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'sortField' => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD),
            'search' => $parameters->getParameter('search_phrase')
        ));

        $query = NULL;
        if ($parameters->hasParameter('search_phrase'))
        {
            $query = new Elastica_Query_Text();
            $query->setFieldQuery('_all', $listState->getSearch());
        }
        else
        {
            $query = new Elastica_Query_MatchAll();
        }

        $filter = new Elastica_Filter_And();
        $filter->addFilter(new Elastica_Filter_Term(array(
            'detailItem.category' => $category->getIdentifier()
        )))->addFilter(
            new Elastica_Filter_Term(array('attributes.duplicates_group.group_leader' => TRUE))
        )->addFilter(
            new Elastica_Filter_Not(
                new Elastica_Filter_Term(array('attributes.marked_deleted' => TRUE))
            )
        );

        $listField = $listConfig->getField($listState->getSortfield());
        $sort = array(
            array($listField->getSortfield() => $listState->getSortDirection()),
            array('_uid' => IListState::SORT_ASC)
        );

        $result = $this->shofiFinder->query($query, $filter, $listState->getOffset(), $listState->getLimit(), $sort);

        $listState->setTotalCount($result->getTotalCount());
        $listState->setData(
            $this->prepareListData(
                $result->getItems(),
                $this->shofiFinder->getWorkflowService()
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
        $options['dedup_url'] = urldecode(htmlspecialchars_decode($routing->gen('shofi.api.dedup')));
        $config['clientSideController']['options'] = $options;
        $config['routeName'] = 'shofi.view_duplicates';
        unset($config['batchActions']['assign_category']);
        unset($config['itemActions']['assign_category']);
        $config['batchActions']["mark_deduplciated"] = "ctrl.markDeduplicated(is_batch, null)";
        return $config;
    }

    protected function prepareListData(array $items, IWorkflowService $workflowService)
    {
        $listData = array();
        $ticketStore = $workflowService->getWorkflowSupervisor()->getWorkflowTicketStore();
        /* @var $workflowItem IWorkflowItem */
        foreach ($items as $workflowItem)
        {
            $curData = array();
            // @todo This findOne query is a potential bottle neck and does not scale!
            // Better: Use the read connection instead of the write connection here.
            // Even Better: Collect all tickets id's and fetch the data in one query.
            $ticket = $ticketStore->fetchByIdentifier($workflowItem->getTicketId());
            if (! $ticket)
            {
                error_log(__METHOD__ . " - Missing ticket for workflow item: " . $workflowItem->getIdentifier());
                continue;
            }
            $curData[] = array(
                'data' => $workflowItem->toArray(),
                'ticket' => array('id' => $ticket->getIdentifier(), 'rev' => $ticket->getRevision()),
                'css_classes' => array('grouped', 'group-start')
            );

            $duplicatesGroup = $workflowItem->getAttribute('duplicates_group');
            $result = $this->shofiFinder->findByIds(
                array_values(array_filter($duplicatesGroup['dups'], function($childId) use ($duplicatesGroup)
                {
                    return $childId !== $duplicatesGroup['gid'];
                }))
            );
            $dupItems = $result->getItems();
            for ($i = 0; $i < $result->getTotalCount(); $i++)
            {
                $duplicateItem = $dupItems[$i];
                if (TRUE === $duplicateItem->getAttribute('no_duplicate'))
                {
                    continue;
                }
                $ticket = $ticketStore->fetchByIdentifier($duplicateItem->getTicketId());
                if (! $ticket)
                {
                    error_log(__METHOD__ . " - Missing ticket for workflow item: " . $duplicateItem->getIdentifier());
                    continue;
                }
                $curData[] = array(
                    'data' => $duplicateItem->toArray(),
                    'ticket' => array('id' => $ticket->getIdentifier(), 'rev' => $ticket->getRevision()),
                    'css_classes' => array('grouped')
                );
            }
            $groupCount = count($curData);
            if (1 >= $groupCount)
            {
                continue;
            }
            $curData[$groupCount - 1]['css_classes'][] = 'group-end';
            $listData = array_merge($listData, $curData);
        }
        return $listData;
    }
}
