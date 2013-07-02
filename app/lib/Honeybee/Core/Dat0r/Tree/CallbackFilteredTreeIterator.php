<?php

namespace Honeybee\Core\Dat0r\Tree;

class CallbackFilteredTreeIterator extends \FilterIterator
{
    protected $tree;

    protected $ignore_level = -1;

    protected $callback;

    public function __construct(ITree $tree, \Closure $callback)
    {
        $this->tree = $tree;
        $this->callback = $callback;

        parent::__construct($this->tree->getIterator());
    }

    public function accept()
    {
        $cur_level = $this->getInnerIterator()->getDepth();

        if ($this->ignore_level > -1 && $cur_level > $this->ignore_level)
        {
            return false;
        }

        $externallyAccepted = call_user_func(
            $this->callback,
            $this->current(),
            $this->key(),
            $this->getInnerIterator()
        );

        if (! $externallyAccepted)
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
