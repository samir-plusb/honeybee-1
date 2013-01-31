<?php

namespace Honeybee\Core\Dat0r\Tree;

class RootNode extends Node
{
    public function getIdentifier()
    {
        return 'root-node';
    }

    public function getLabel()
    {
        return 'Root';
    }
}
