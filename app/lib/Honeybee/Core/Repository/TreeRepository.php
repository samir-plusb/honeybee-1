<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\DocumentCollection;

class TreeRepository extends GenericRepository
{
    public function find($query = NULL, $limit = 0, $offset = 0)
    {
        $tree = NULL;

        if ($treeData = $this->getFinder()->find($query))
        {
            $tree = new Tree\Tree($this->getModule(), $treeData);
        }

        return $tree;
    }
    
    public function read($identifier)
    {
        return $this->find($identifier);
    }

    public function write($tree)
    {
        $storage = $this->getStorage();

        $this->getStorage()->writeOne($tree);
    }

    public function delete($tree)
    {
        $errors = array();

        if ($data instanceof ITree)
        {
            $this->getStorage()->delete(
                $tree->getIdentifier(), 
                $tree->getRevision()
            );
        }
        else
        {
            throw new InvalidArgumentException(
                'Only ITree instances allowed as $data argument.'
            );
        }

        return $errors;
    }
}
