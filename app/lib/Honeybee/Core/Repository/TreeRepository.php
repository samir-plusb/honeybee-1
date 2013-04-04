<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Tree;

class TreeRepository extends BaseRepository
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
        $errors = array();

        if ($tree instanceof Tree\ITree)
        {
            $storage = $this->getStorage();
            $data = $tree->toArray(NULL, FALSE);
            $data['type'] = get_class($tree);

            $revision = $this->getStorage()->write($data);
            $tree->setRevision($revision);
        }
        else
        {
            throw new \InvalidArgumentException(
                'Only instances of ITree are allowed as $data argument; ' . get_class($tree) . ' given.'
            );
        }

        return $errors;
    }

    public function delete($tree)
    {
        $errors = array();

        if ($data instanceof Tree\ITree)
        {
            $this->getStorage()->delete(
                $tree->getIdentifier(), 
                $tree->getRevision()
            );
        }
        else
        {
            throw new \InvalidArgumentException(
                'Only instances of ITree are allowed as $data argument; ' . get_class($tree) . ' given.'
            );
        }

        return $errors;
    }
}
