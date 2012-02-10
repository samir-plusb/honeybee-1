<?php

/**
 * The ItemFinderModel is responseable for finding news-items and provides several methods to do so.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Lib
 */
class ItemFinder
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the default limit used within the definitions of all finder methods that take a limit.
     */
    const DEFAULT_LIMIT = 50;

    /**
     * String representation of the descending sorting.
     */
    const SORT_DESC = 'desc';

    /**
     * String representation of the ascending sorting.
     */
    const SORT_ASC = 'asc';

    /**
     * Name of the elastic search index that is queried when gathering news-item information.
     */
    const ES_IDX_NAME = 'midas';

    /**
     * Name of the elastic search type that we query for news-items.
     */
    const ES_TYPE_NAME = 'item';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An array that maps fieldname aliases to their corresponding realnames.
     * Is used to have a more comfortable handling when passing sort fields to the finder api.
     *
     * @var array
     */
    private static $sortMapping = array(
        'title'        => 'importItem.title.title_sortable',
        'source'       => 'importItem.source.source_sortable',
        'timestamp'    => 'importItem.created.date',
        'state'        => 'currentState.step',
        'category'     => 'importItem.category.category_sortable',
        'owner'        => 'currentState.owner',
        'priority'     => 'contentItems.priority',
        'publish_date' => 'contentItems.publishDate'
    );

    /**
     * The client used to talk to elastic search.
     *
     * @var Elastica_Client
     */
    protected $elasticClient;

    /**
     * !HACK - Violation of SRP!
     * Holds the id of an item that is currently loaded into the frontends editview by an user
     * that is editing the respective item.
     * If this property is set, then the addEditingStreamFilter method is invoked upon
     * all query filters, adding extra conditions that serve the purpose of finding the
     * 'NEXT' or 'PREVIOUS' item suitable for the user(news-editor) to edit.
     *
     * @var string
     */
    protected $currentItemId = NULL;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Create a new ItemFinder instance.
     *
     * @param Elastica_Client $elasticClient
     */
    public function __construct(Elastica_Client $elasticClient)
    {
        $this->elasticClient = $elasticClient;
    }

    /**
     * Fetch all items within the current range of offset and limit ordered by the given sorting parameters.
     *
     * @param string $sortField
     * @param string $sortDirection
     * @param int $offset
     * @param int $limit
     *
     * @return array An array holding the WorkflowItems for the current limit&offset and the total-count.
     * @see self::hydrateResult() For documentation on the return value's structure.
     */
    public function fetchAll($sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        return $this->fireNewsItemQuery(
            new Elastica_Query(new Elastica_Query_MatchAll()), $offset, $limit,
            $this->prepareSortingParams($sortField, $sortDirection)
        );
    }

    /**
     * Fetch all items that match the given searchPhrase
     * within the current range of offset and limit ordered by the given sorting parameters.
     *
     * At the moment we support two search modes that depend on the number of whitespace separated terms
     * that are passed as the searchPhrase:
     *
     * *1. wildcard-search:
     *     Is used when only one term is provided within the search phrase and allows you to use a wildcard syntax.
     *     Examples: "wildthi*" or "wild*thi" or "*thing" etc.
     * *2. term-search:
     *     Is used when ore than one whitespace separated term is provided within the searchPhrase
     *     and will return only records that contain all of the terms indpendant from the term order.
     *     Examples: "wild thing cars"
     *
     * @param string $sortField
     * @param string $sortDirection
     * @param int $offset
     * @param int $limit
     *
     * @return array An array holding the WorkflowItems for the current limit&offset and the total-count.
     * @see self::hydrateResult() For documentation on the return value's structure.
     */
    public function search($searchPhrase, $sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        $query = new Elastica_Query();
        $terms = explode(' ', $searchPhrase);
        if (1 === count($terms))
        {
            $query->setQuery(
                new Elastica_Query_Wildcard('_all', $searchPhrase)
            );
        }
        else
        {
            $termQuery = new Elastica_Query_Terms('_all', $terms);
            $termQuery->setMinimumMatch(count($terms));
            $query->setQuery($termQuery);
        }

        return $this->fireNewsItemQuery(
            $query, $offset, $limit,
            $this->prepareSortingParams($sortField, $sortDirection)
        );
    }

    /**
     * Return all items that are 'near' to the given WGS84 coordinates,
     * whereas 'near' is specified by the passed distance.
     * The items are returned according to the specified range and order parameters.
     *
     * @param array $where Must contain the following key: dist(ance), lon(gitude) and lat(itude).
     * @param string $sortField
     * @param string $sortDirection
     * @param int $offset
     * @param int $limit
     *
     * @throws InvalidArgumentException If the $where data is corrupt or missing.
     *
     * @return array An array holding the WorkflowItems for the current limit&offset and the total-count.
     * @see self::hydrateResult() For documentation on the return value's structure.
     */
    public function nearBy(array $where, $sortField, $sortDirection = self::SORT_DESC, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        if (! isset($where['dist']) || ! isset($where['lon']) || ! isset($where['lat']))
        {
            throw new InvalidArgumentException(
                "Missing information on where you would like to search the nearby items." . PHP_EOL .
                "Be sure to pass dist, lon and lat inside the \$where array."
            );
        }

        $query = new Elastica_Query(new Elastica_Query_MatchAll());
        $geoDistanceFilter = new Elastica_Filter_And();

        $query->setFilter(
            $geoDistanceFilter->addFilter(
                $this->buildBasicNewsFilter()
            )->addFilter(
                new Elastica_Filter_GeoDistance(
                    'contentItems.location.coordinates',
                    $where['lat'],
                    $where['lon'],
                    $where['dist']
                )
            )
        )->setLimit($limit)
         ->setFrom($offset)
         ->addSort(
             $this->prepareSortingParams($sortField, $sortDirection)
        );

        $index = $this->elasticClient->getIndex(self::ES_IDX_NAME);
        return $this->hydrateResult(
            $index->getType(self::ES_TYPE_NAME)->search($query)
        );
    }

    /**
     * Enable the 'edit stream' mode.
     * !SPECIAL - @see self::$currentItemId!
     *
     * @param string $curItemId The item to consider as currently 'opened for edit' in the gui.
     */
    public function enableEditFilter($curItemId)
    {
        $this->currentItemId = $curItemId;
    }

    /**
     * Disable the 'edit stream' mode.
     * !SPECIAL - @see self::$currentItemId!
     */
    public function disableEditFilter()
    {
        $this->currentItemId = NULL;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Takes a sorting-field alias and -direction and validates them
     * against our list of supported sorting-field aliases,
     * thereby translating the alias to it's corresponding realname.
     *
     * Example structure:
     * <pre>
     * array(
     *     array('importItem.created.date' => 'desc'),
     *     array('_uid' => 'asc')
     * )
     * </pre>
     * Notice 10.02.2012
     * The elastic search' internal _uid field is added to keep the sorting stable
     * on the couchdb river provided data inside elastic search.
     * If you do not understand this (I know the comment is too short) ask Thorsten Schmitt-Rink
     *
     * @param string $sortField
     * @param string $sortDirection
     *
     * @throws InvalidArgumentException If the given sortField is not supported.
     *
     * @return array An array that can be passed to the Elastica_Query's addSort method as is.
     */
    protected function prepareSortingParams($sortField, $sortDirection)
    {
        if (! isset(self::$sortMapping[$sortField]))
        {
            throw new InvalidArgumentException(
                "Invalid sort field given. The field '$sortField' is not supported."
            );
        }
        $esSortFieldName = self::$sortMapping[$sortField];
        return array(
            array($esSortFieldName => $sortDirection),
            array('_uid' => self::SORT_ASC)
        );
    }

    /**
     * Fire the given elastic search query against the news items index
     * and return the hydrated result.
     *
     * @param Elastica_Query $query
     * @param int $offset
     * @param int $limit
     * @param array $sorting
     *
     * @return array
     * @see self::hydrateResult() For documentation on the return value's structure.
     */
    protected function fireNewsItemQuery(Elastica_Query $query, $offset, $limit, array $sorting)
    {
        $index = $this->elasticClient->getIndex(self::ES_IDX_NAME);

        return $this->hydrateResult(
            $index->getType(self::ES_TYPE_NAME)->search(
                $query->setFilter(
                    $this->addEditingStreamFilter(
                        $this->buildBasicNewsFilter()
                    )
                )->setLimit($limit)
                 ->setFrom($offset)
                 ->addSort($sorting)
            )
        );
    }

    /**
     * Builds the basic filter applied to all queries of this class.
     * The filter defines only items within the news workflow that are not deleted.
     *
     * @return Elastica_Filter_And
     */
    protected function buildBasicNewsFilter()
    {
        // make sure we only retrieve 'news' that is 'not-deleted'
        $notDeletedNewsFilter = new Elastica_Filter_And();

        return $notDeletedNewsFilter->addFilter(
            new Elastica_Filter_Term(array('currentState.workflow' => 'news'))
        )->addFilter(
            new Elastica_Filter_Not(
                new Elastica_Filter_Term(array('currentState.step' => 'delete_news'))
            )
        );
    }

    /**
     * Hydrates a given elastic search query result into an array of IWorkflowItems
     * and returns them along with a totalCount for the query that was issued.
     *
     * An example of the returned data structure:
     * <pre>
     * array(
     *     'items' => array(
     *         [0] => (WorkflowItem)
     *     ),
     *     'totalCount' => 1024
     * )
     * </pre>
     *
     * @param Elastica_ResultSet $result
     *
     * @return array
     */
    protected function hydrateResult(Elastica_ResultSet $result)
    {
        $items = array();
        /* @var $items Elastica_Result */
        foreach($result->getResults() as $doc)
        {
            $items[] = new WorkflowItem($doc->getData());
        }
        return array(
            'items' => $items,
            'totalCount' => $result->getTotalHits()
        );
    }

    /**
     * !SPECIAL! This method adds filter's that serve the 'edit-stream' modes purpose.
     * Corresponding filter criteria are:
     * item has no owner, item is new or item is the item currently being edited.
     * @see self::$currentItemId doc for more information.
     *
     * @param Elastica_Filter_Abstract $filter
     *
     * @return Elastica_Filter_Or
     */
    protected function addEditingStreamFilter(Elastica_Filter_Abstract $filter)
    {
        // if the 'special browse editable only' mode is off, there's nothing to do
        if (NULL === $this->currentItemId)
        {
            return $filter;
        }

        // make sure only 'new' and 'available' items are retrieved.
        $filter->addFilter(
            new Elastica_Filter_Term(array('currentState.step' => 'refine_news'))
        )->addFilter(
            new Elastica_Filter_Term(array('currentState.owner' => 'nobody'))
        );

        $idOrFilter = new Elastica_Filter_Or();
        // add the item that 'we are currently editing' to the result in any case
        // as this is the guy we will look for afterwards in order to determine
        // what the next or previous item would be.
        return $idOrFilter->addFilter(
            new Elastica_Filter_Ids('item', array($this->currentItemId))
        )->addFilter($filter);
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
