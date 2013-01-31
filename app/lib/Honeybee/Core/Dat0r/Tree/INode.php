<?php

namespace Honeybee\Core\Dat0r\Tree;

interface INode
{
    public function getIdentifier();

    public function getLabel();

    public function getParent();

    public function setParent(INode $parent);

    public function hasChildren();

    public function getChildren();

    public function addChild(INode $child);

    public function getChildAt($index);

    public function getFirstChild();

    public function removeChild(INode $child);

    public function toArray($level = NULL, $expand = TRUE);
}
