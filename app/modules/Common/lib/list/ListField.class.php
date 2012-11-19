<?php

class ListField implements IListField
{
    protected $name;

    protected $valuefield;

    protected $sortfield;

    protected $renderer = 'DefaultListValueRenderer';

    public static function create(array $data = array())
    {
        return empty($data) ? new static : new static($data);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValueField()
    {
        return $this->valuefield;
    }

    public function setValueField($valuefield)
    {
        $this->valuefield = $valuefield;
    }

    public function hasValueField()
    {
        return NULL !== $this->valuefield;
    }

    public function getSortField()
    {
        return $this->sortfield;
    }

    public function setSortField($sortfield)
    {
        $this->sortfield = $sortfield;
    }

    public function hasSortField()
    {
        return NULL !== $this->sortfield;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function hasRenderer()
    {
        return NULL !== $this->renderer;
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
