<?php

interface IListState extends IDataObject
{
    const MODE_SUGGEST = 'suggest';

    const MODE_SEARCH = 'search';

    const DEFAULT_LIMIT = 50;

    const SORT_DESC = 'desc';

    const SORT_ASC = 'asc';
    
    public function setTotalCount($totalCount);

    public function getTotalCount();

    public function setOffset($offset);

    public function getOffset();

    public function setLimit($limit);

    public function getLimit();

    public function setData(array $data);

    public function getData();

    public function hasSearch();

    public function setSearch($search);

    public function getSearch();

    public function setFilter(array $filter);

    public function getFilter();

    public function hasFilter();

    public function getSortDirection();

    public function getSortField();

    public function setSearchMode($searchMode);

    public function getSearchMode();
}

?>
