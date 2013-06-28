<?php

namespace Honeybee\Core\Dat0r\Tree;

interface ITree
{
    public function getIdentifier();

    public function getRevision();

    public function setRevision($revision);

    public function getRootNode();

    public function toArray();

    public function getIterator();
}
