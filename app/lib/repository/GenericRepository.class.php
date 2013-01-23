<?php

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\DocumentCollection;
use Honeybee\Core\Dat0r\Document;

class GenericRepository implements IRepository
{
    private $module;

    private $finder;

    private $storage;

    public function __construct(Module $module)
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
        $documents = new DocumentCollection();

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
    
    // @todo add a get method to the finder and use it instead of the storage here.
    public function read($identifier)
    {
        $documents = new DocumentCollection();

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
            $documents->add(
                $this->module->createDocument($data)
            );
        }
        
        return $documents;
    }

    public function write($data)
    {
        $errors = array();

        if ($data instanceof Document)
        {
            $this->storage->writeOne($data);
        }
        else if ($data instanceof DocumentCollection)
        {
            $errors = $this->storage->writeMany($data);
        }
        else
        {
            throw new InvalidArgumentException(
                'Only Honeybee\Core\Dat0r\Document and DocumentCollection allowed as $data argument.'
            );
        }

        return $errors;
    }

    public function delete($data)
    {
        $errors = array();

        if ($data instanceof Document)
        {
            $this->storage->delete($data);
        }
        else
        {
            throw new InvalidArgumentException(
                'Only Document and DocumentCollection allowed as $data argument.'
            );
        }

        return $errors;
    }
}
