<?php

namespace Honeybee\Core\Dat0r\Tree;

class Tree implements ITree
{
    protected $rootNode;

    public function __construct(RootNode $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function getRootNode()
    {
        return $this->rootNode;
    }

    public function toArray()
    {
        return $this->rootNode->toArray();
    }
}
