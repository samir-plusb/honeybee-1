<?php

namespace Honeybee\Core\Dat0r\Tree;

class RecursiveNodeIterator implements \RecursiveIterator
{
    protected $node;

    protected $children;

    protected $cursorPos = 0;

    public function __construct(INode $node)
    {
        $this->node = $node;
        $this->children = $this->node->getChildren();
    }

    public function next() 
    {
        $this->cursorPos++;
    }
   
    public function rewind()
    {
        $this->cursorPos = 0;
    }
   
    public function key() 
    {
        return $this->cursorPos;
    }

    public function valid() 
    {
        return isset($this->children[$this->cursorPos]);
    }

    public function current() 
    {
        return $this->children[$this->cursorPos];
    }

    public function hasChildren()
    {
        return $this->current()->hasChildren();
    }

    public function getChildren()
    {
        return new static($this->current());
    }
}
