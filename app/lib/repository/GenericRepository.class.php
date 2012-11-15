<?php

class GenericRepository implements IRepository
{
    private $finder;

    private $storage;

    public function __construct(IFinder $finder, IStorage $storage)
    {
        $this->finder = $finder;
        $this->storage = $storage;
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function getStorage()
    {
        return $this->storage;
    }
    
    public function find($query = NULL, $limit = 0, $offset = 0)
    {
        $documents = array();

        if ($query && 1 === $limit)
        {
            if (($document = $this->finder->findOne($query)))
            {
                $documents[] = $this->finder->findOne($query);
            }
        }
        else if ($query)
        {
            $documents = $this->finder->findMany($query, $limit, $offset);
        }
        else
        {
            $documents = $this->finder->findAll($limit, $offset);
        }

        return $documents;
    }

    public function read($identifier)
    {
        return is_array($identifier) 
            ? $this->storage->readMany($identifier)
            : $this->storage->readOne($identifier);
    }

    public function write($data)
    {
        $result = FALSE;

        // @todo we need to communicate errors nice and transparently somehow.
        // maybe return a a result/report object.
        if ($this->mayWrite($data))
        {
            if (is_array($data))
            {
                $result = $this->storage->writeMany($data);
            }
            else
            {
                $result = $this->storage->writeOne($data);
            }
        }

        return $result;
    }

    // @todo maybe we should ony allow documents that have been retrieved by 'read'
    // to be stored and start to track them when they are exposed as a 'read' result.
    // something like: if (in_array($document->getIdentifier(), $this->trackedIdentifiers))
    private function mayWrite($data)
    {
        $mayWrite = TRUE;

        if (is_array($data))
        {
            foreach ($data as $document)
            {
                if (! ($data instanceof HoneybeeDocument))
                {
                    $mayWrite = FALSE;
                }
            }
        }
        else if (! ($data instanceof HoneybeeDocument))
        {
            $mayWrite = FALSE;
        }

        return $mayWrite;
    }
}
