<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Finder\IFinder;
use Honeybee\Core\Storage\IStorage;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\DocumentCollection;

class DocumentRepository extends BaseRepository
{
    public function find($query = NULL, $limit = 100000, $offset = 0)
    {
        $documents = new DocumentCollection();

        $result = (NULL === $query) 
            ? $this->getFinder()->fetchAll($limit, $offset)
            : $this->getFinder()->find($query, $limit, $offset);

        foreach ($result['data'] as $documentData)
        {
            $documents->add(
                $this->getModule()->createDocument($documentData)
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
                if (($data = $this->getStorage()->read($curIdentifier)))
                {
                    $documents->add(
                        $this->getModule()->createDocument($data)
                    );
                }
            }
        }
        else if (($data = $this->getStorage()->read($identifier)))
        {
            $documents->add(
                $this->getModule()->createDocument($data)
            );
        }
        
        return $documents;
    }

    public function write($data)
    {
        $errors = array();

        if ($data instanceof Document)
        {
            $this->getStorage()->writeOne($data);
        }
        else if ($data instanceof DocumentCollection)
        {
            $errors = $this->getStorage()->writeMany($data);
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
            $this->getStorage()->delete(
                $data->getIdentifier(), 
                $data->getRevision()
            );
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
