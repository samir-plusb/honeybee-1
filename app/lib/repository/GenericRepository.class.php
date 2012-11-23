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

        $result = (NULL === $query) 
            ? $this->finder->fetchAll($limit, $offset)
            : $this->finder->find($query, $limit, $offset);

        foreach ($result['data'] as $documentData)
        {
            $documents->add(
                $this->module->createDocument($documentData)
            );
        }

        return array(
            'documents' => $documents,
            'totalCount' => $result['totalCount']
        );
    }

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
        $errors = array();

        if ($data instanceof HoneybeeDocument)
        {
            $this->storage->writeOne($data);
        }
        else if ($data instanceof HoneybeeDocumentCollection)
        {
            $errors = $this->storage->writeMany($data);
        }
        else
        {
            throw new InvalidArgumentException(
                "Only HoneybeeDocument and HoneybeeDocumentCollection allowed as $data argument."
            );
        }

        return $errors;
    }
}
