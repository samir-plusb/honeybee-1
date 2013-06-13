<?php

namespace Honeybee\Core\Import\Filter;

class FilterList implements \Countable, \ArrayAccess, \Iterator
{
    private $filters;

    public function __construct(array $filters = array())
    {
        foreach ($filters as $filter)
        {
            $this->add($filter);
        }
    }

    public function first()
    {
        return 1 <= $this->count() ? $this->filters[0] : FALSE;
    }

    public function add(IFilter $filter)
    {
        $this->filters[] = $filter;
    }

    public function remove(IFilter $filter)
    {
        $offset = array_search($filter, $this->filters, TRUE);

        $this->offsetUnset($offset);
    }

    public function toArray()
    {
        $data = array_map(function($filter)
        {
            return $filter->toArray();
        }, $this->filters);

        return $data;
    }

    public function count()
    {
        return count($this->filters);
    }

    public function offsetExists($offset)
    {
        return isset($this->filters[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->filters[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (NULL === $offset)
        {
            $this->filters[] = $value;
        }
        else
        {
            $this->filters[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        array_splice($this->filters, $offset, 1);
    }

    public function current()
    {
        if ($this->valid())
        {
            return current($this->filters);
        }
        else
        {
            return FALSE;
        }
    }

    public function key()
    {
        return key($this->filters);
    }

    public function next()
    {
        return next($this->filters);
    }

    public function rewind()
    {
        reset($this->filters);
    }

    public function valid()
    {
        return NULL !== key($this->filters);
    }
}
