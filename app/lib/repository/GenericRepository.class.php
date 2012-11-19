<?php

class GenericRepository implements IRepository
{
    private $module;

    private $finder;

    private $storage;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function initialize(IFinder $finder = NULL, IStorage $storage = NULL)
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
        $documents = new HoneybeeDocumentCollection();

        if ($query && 1 === $limit)
        {
            if (($data = $this->finder->findOne($query)))
            {
                $documents->add(
                    $this->module->createDocument($data)
                );
            }
        }
        else if ($query)
        {
            $data = $this->finder->findMany($query, $limit, $offset);
        }
        else
        {
            $data = $this->finder->findAll($limit, $offset);
        }

        return $documents;
    }

    // @todo Use the finder (read connection) here.
    public function read($identifier)
    {
        $documents = new HoneybeeDocumentCollection();

        if (is_array($identifier))
        {
            foreach ($identifier as $curIdentifier)
            {
                if (($data = $this->storage->read($curIdentifier)))
                {
                    $documents->add(
                        $this->module->createDocument($data)
                    );
                }
            }
        }
        else if (($data = $this->storage->read($identifier)))
        {
            $documents[] = $documents->add(
                $this->module->createDocument($data)
            );
        }
        
        return $documents;
    }

    public function write($data)
    {
        // @todo we need to communicate errors nice and transparently somehow.
        // maybe return a a result/report object.
        $errors = array();

        try
        {
            if ($this->mayWrite($data))
            {
                if (is_array($data))
                {
                    $errors = $this->storage->writeMany($data);
                }
                else
                {
                    $this->storage->writeOne($data);
                }
            }
        }
        catch (Exception $e)
        {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }

    // @todo maybe we should ony allow documents that have been retrieved by 'read'
    // to be stored and start to track them when they are exposed as a 'read' result.
    // something like: if (in_array($document->getIdentifier(), $this->trackedIdentifiers))
    protected function mayWrite($data)
    {
        $mayWrite = TRUE;

        if (is_array($data))
        {
            foreach ($data as $document)
            {
                if (! ($document instanceof HoneybeeDocument))
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
