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
        $result = NULL;

        if (is_array($data))
        {
            $result = $this->storage->writeMany($data);
        }
        else if ($data instanceof HoneybeeDocument)
        {
            $result = $this->storage->writeOne($data);
        }
        else
        {
            throw new InvalidArgumentException(
                "The given data type is not supported by the repositories write method."
            );
        }

        return $result;
    }
}
