<?php

class ProjectIdSequenceMap extends BaseDocument
{
    protected $revision;
    
    protected $ids;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function addId($receipient)
    {
        $nextId = array_search($receipient, $this->ids);
        if (FALSE === $nextId)
        {
            $nextId = count($this->ids);
            $this->ids[] = $receipient;
        }

        return $nextId + 1;
    }
}
