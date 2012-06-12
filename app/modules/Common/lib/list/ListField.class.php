<?php

class ListField extends BaseDataObject
{
    protected $name;

    protected $valuefield;

    protected $sortfield;

    protected $renderer = 'DefaultListValueRenderer';

    public static function fromArray(array $data = array())
    {
        return new self($data);
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
}
