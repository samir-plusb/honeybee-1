<?php

namespace Honeybee\Core\Dat0r\Tree;

abstract class Node implements INode
{
    protected $parent;

    protected $children;

    public function __construct(array $children = array())
    {
        $this->children = array();

        foreach ($children as $child)
        {
            $this->addChild($child);
        }
    }

    public function hasParent()
    {
        return $this->parent instanceof INode;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(INode $parent)
    {
        $this->parent = $parent;
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
        if (! in_array($child, $this->children, TRUE))
        {
            $child->setParent($this);
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

    public function toArray($level = NULL, $expand = TRUE)
    {
        $children = array();

        foreach ($this->getChildren() as $childNode)
        {
            $children[] = $childNode->toArray($level, $expand);
        }

        $expandedData = array();
        if (TRUE === $expand)
        {
            $expandedData = array(
                'label' => $this->getLabel(),
                'parent' => $this->hasParent() ? $this->getParent()->getIdentifier() : NULL,
            );
        }

        return array_merge(
            $expandedData,
            array('identifier' => $this->getIdentifier(), 'children' => $children)
        );
    }
}
