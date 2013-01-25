<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Finder\ElasticSearch\QueryBuilder;
use Honeybee\Core\Finder\ElasticSearch\SuggestQueryBuilder;

use \ListConfig;
use \IListState;

abstract class BaseService implements IService
{
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function save(Document $document)
    {
        $repository = $this->module->getRepository();
        $errors = $repository->write($document);

        if (! empty($errors))
        {
            throw new Exception(
                "Unexpected errors occured trying to store data." . 
                PHP_EOL . implode("\n", $errors)
            );
        }
    }

    public function get($identifier)
    {
        $document = NULL;

        $repository = $this->module->getRepository();
        $documents = $repository->read($identifier);

        if (1 === $documents->count())
        {
            $document = $documents[0];
        }

        return $document;
    }

    public function delete(Document $document, $markOnly = TRUE)
    {
        if ($markOnly)
        {
            $meta = $document->getMeta();
            $meta['is_deleted'] = TRUE;
            $document->setMeta($meta);
            
            $this->save($document);
        }
        else
        {
            // this actually is destructive, only use if you REALLY want to delete.
            $this->module->getRepository()->delete($document);
        }
    }

    public function fetchListData(ListConfig $config, IListState $state)
    {
        // @todo Introduce a factory setting to allow inject the implementor for building queries.
        $queryBuilder = new QueryBuilder();
        $query = $queryBuilder->build(
            array('config' => $config, 'state' => $state)
        );

        $offset = $state->getOffset();
        $limit = $state->getLimit();
        $repository = $this->module->getRepository();
        
        return $repository->find($query, $limit, $offset);
    }

    public function suggestDocuments($term, $field, $sorting = array())
    {
        $repository = $this->module->getRepository();
        $queryBuilder = new SuggestQueryBuilder();
        $query = $queryBuilder->build(
            array('term' => $term, 'field' => $field, 'sorting' => $sorting)
        );

        return $repository->find($query, 50, 0);
    }
}
