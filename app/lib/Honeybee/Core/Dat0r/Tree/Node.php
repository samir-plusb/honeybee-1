<?php

namespace Honeybee\Core\Dat0r\Tree;

abstract class Node implements INode
{
    protected $children;

    public function __construct(array $children = array())
    {
        $this->children = array();

        foreach ($children as $child)
        {
            $this->addChild($child);
        }
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getFirstChild()
    {
        return $this->getChildAt(0);
    }

    public function getChildAt($index)
    {
        return isset($this->children[$index]) ? $this->children[$index] : NULL;
    }

    public function addChild(INode $child)
    {
        if (! in_array($child, $this->children))
        {
            $this->children[] = $child;
        }
    }

    public function removeChild(INode $child)
    {
        if (($pos = array_search($child, $this->children)))
        {
            array_splice($this->children, $pos, 1);
        }
    }

    public function toArray()
    {
        $children = array();

        foreach ($this->getChildren() as $childNode)
        {
            $children[] = $childNode->toArray();
        }

        return array(
            'identifier' => $this->getIdentifier(),
            'label' => $this->getLabel(),
            'children' => $children
        );
    }
}
