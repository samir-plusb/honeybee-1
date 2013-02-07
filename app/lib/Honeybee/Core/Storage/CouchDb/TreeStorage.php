<?php

namespace Honeybee\Core\Storage\CouchDb;

use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Tree\ITree;
use Honeybee\Agavi\Database\CouchDb\Database;
use Honeybee\Agavi\Database\CouchDb\ClientException;

class TreeStorage extends BaseStorage
{
    public function read($identifier, $revision = NULL)
    {
        // throw NotSupportedException ?
        return NULL;
    }

    public function writeOne($tree)
    {
        $couchDb = $this->getDatabase()->getConnection();
        $data = $this->mapDomainDataToCouchDb(
            get_class($tree), 
            $tree->toArray(NULL, FALSE),
            NULL
        );

        $result = $couchDb->storeDoc(NULL, $data);

        if(isset($result['error']) && 'conflict' === $result['error'])
        {
            $data[self::COUCH_REV] = $couchDb->statDoc(NULL, $tree->getIdentifier());
            $result = $couchDb->storeDoc(NULL, $data);
        }

        if (isset($result['ok']) && isset($result['rev']))
        {
            $tree->setRevision($result['rev']);
        }
        else
        {
            throw new \Exception(
                "Failed to store tree: " . $tree->getIdentifier()
            );
        }
    }

    public function writeMany(array $trees)
    {
        foreach ($trees as $tree)
        {
            $this->writeOne($tree->toArray());
        }
    }

    public function delete($identifier, $revision = NULL)
    {
        $couchDb = $this->getDatabase()->getConnection();
        $couchDb->deleteDoc(NULL, $identifier, $revision);
    }
}
