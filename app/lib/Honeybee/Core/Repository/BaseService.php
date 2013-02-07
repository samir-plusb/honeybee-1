<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Finder\ElasticSearch\QueryBuilder;
use Honeybee\Core\Finder\ElasticSearch\SuggestQueryBuilder;
use Elastica;

use ListConfig;
use IListState;

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

    public function getMany(array $identifiers = array(), $limit = 10000, $offset = 0)
    {
        $query = NULL;

        if (empty($identifiers))
        {
            $query = Elastica\Query::create(NULL);
        }
        else
        {
            $query = Elastica\Query::create(new Elastica\Filter\Ids(
                $this->module->getOption('prefix'), 
                array_unique($ids)
            ));
        }

        $repository = $this->module->getRepository();

        return $repository->find($query, $limit, $offset);
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

    public function getTree($treeName = 'tree-default')
    {
        $this->verifyModuleTreeAccess();

        $repository = $this->module->getRepository('tree');
        $tree = $repository->read($treeName);

        return $tree ? $tree : $this->createTree($treeName);
    }

    public function storeTree(Tree\Tree $tree)
    {
        $this->verifyModuleTreeAccess();

        $repository = $this->module->getRepository('tree');
        $repository->write($tree);
    }

    protected function createTree($treeName)
    {
        $this->verifyModuleTreeAccess();        

        return new Tree\Tree(
            $this->module, 
            array('identifier' => $treeName)
        );
    }

    protected function verifyModuleTreeAccess()
    {
        if (! $this->module->isActingAsTree())
        {
            throw new \Exception(sprintf(
                "The module %s is not acting as a tree. Please make sure you have apllied the acts_as_tree option.",
                $this->module->getName()
            ));
        }
    }
}
