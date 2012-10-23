<?php

class ListState extends FreezableDataObject implements IListState
{
    protected $data = array();

    protected $totalCount = NULL;

    protected $limit = 0;

    protected $offset = 0;

    protected $search = NULL;

    protected $sortDirection = NULL;

    protected $sortField = NULL;

    protected $filter = array();

    protected $searchMode = self::MODE_SEARCH;

    public static function fromArray(array $data = array())
    {
        return new ListState($data);
    }

    public function setData(array $data)
    {
        $this->breakWhenFrozen();
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setTotalCount($totalCount)
    {
        $this->breakWhenFrozen();
        $this->totalCount = (int) $totalCount;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function setLimit($limit)
    {
        $this->breakWhenFrozen();
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function hasLimit()
    {
        return NULL !== $this->limit;
    }

    public function setOffset($offset)
    {
        $this->breakWhenFrozen();
        $this->offset = $offset;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function hasOffset()
    {
        return NULL !== $this->offset;
    }

    public function setSearch($search)
    {
        $this->breakWhenFrozen();
        $this->search = $search;
    }

    public function hasSearch()
    {
        return ! empty($this->search);
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setFilter(array $filter)
    {
        $this->breakWhenFrozen();
        $this->filter = $filter;
    }

    public function hasFilter()
    {
        return ! empty($this->filter);
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setSortDirection($direction)
    {
        $this->breakWhenFrozen();
        $this->sortDirection = $direction;
    }

    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    public function setSortField($field)
    {
        $this->breakWhenFrozen();
        $this->sortField = $field;
    }

    public function getSortField()
    {
        return $this->sortField;
    }

    public function setSearchMode($searchMode)
    {
        $this->breakWhenFrozen();
        $this->searchMode = $searchMode;
    }

    public function getSearchMode()
    {
        return $this->searchMode;
    }
}

?>