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

    public function write($document)
    {
        if ($document instanceof Document)
        {
            $document->onBeforeWrite();
            
            $data = $document->toArray();
            $data['type'] = get_class($document);
            $revision = $this->getStorage()->write($data);
            $document->setRevision($revision);
        }
        else
        {
            throw new \InvalidArgumentException('Only Document instances are allowed as $data argument.');
        }
    }

    public function delete($document)
    {
        $errors = array();

        if ($document instanceof Document)
        {
            $this->getStorage()->delete(
                $document->getIdentifier(), 
                $document->getRevision()
            );
        }
        else
        {
            throw new \InvalidArgumentException('Only Document instances allowed for the $data method parameter.');
        }

        return $errors;
    }
}
