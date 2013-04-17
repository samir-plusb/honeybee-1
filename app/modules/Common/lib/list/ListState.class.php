<?php

class ListState implements IListState
{
    protected $data = array();

    protected $totalCount = NULL;

    protected $limit = NULL;

    protected $offset = NULL;

    protected $search = NULL;

    protected $sortDirection = NULL;

    protected $sortField = NULL;

    protected $filter = array();

    protected $referenceField = FALSE;

    protected $searchMode = self::MODE_SEARCH;

    public static function create(array $data = array())
    {
        return empty($data) ? new static : new static($data);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setTotalCount($totalCount)
    {
        $this->totalCount = (int)$totalCount;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
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
        $this->offset = (int)$offset;
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
        $this->sortDirection = $direction;
    }

    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    public function setSortField($field)
    {
        $this->sortField = $field;
    }

    public function getSortField()
    {
        return $this->sortField;
    }

    public function setSearchMode($searchMode)
    {
        $this->searchMode = $searchMode;
    }

    public function getSearchMode()
    {
        return $this->searchMode;
    }

    public function getReferenceField()
    {
        return $this->referenceField;
    }

    public function isInSelectOnlyMode()
    {
        return ! empty($this->referenceField);
    }

    protected function __construct(array $data = array())
    {
        if (! empty($data))
        {
            $this->hydrate($data);
        }
    }

    protected function hydrate(array $data)
    {
        foreach (array_keys(get_class_vars(get_class($this))) as $prop)
        {
            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
                else
                {
                    $this->$prop = $data[$prop];
                }
            }
        }
    }
}
