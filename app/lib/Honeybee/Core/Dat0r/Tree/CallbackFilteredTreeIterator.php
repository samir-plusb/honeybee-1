<?php

namespace Honeybee\Core\Dat0r\Tree;

class CallbackFilteredTreeIterator extends \CallbackFilterIterator
{
    protected $tree;

    protected $ignore_level = -1;

    public function __construct(ITree $tree, $callback)
    {
        $this->tree = $tree;

        parent::__construct($this->tree->getIterator(), $callback);
    }

    public function accept()
    {
        $cur_level = $this->getInnerIterator()->getDepth();

        if ($this->ignore_level > -1 && $cur_level > $this->ignore_level)
        {
            return false;
        }

        if (! parent::accept())
        {
            $this->ignore_level = $cur_level;

            return false;
        }
        else
        {
            $this->ignore_level = -1;
        }

        return true;
    }

    public function rewind()
    {
        parent::rewind();

        $this->ignore_level = -1;
    }
}
