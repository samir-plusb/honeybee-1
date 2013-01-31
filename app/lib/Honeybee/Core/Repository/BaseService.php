<?php

namespace Honeybee\Core\Repository;

use Honeybee\Core\Dat0r\Module;
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

    public function getTree($name = 'tree')
    {
        $repository = $this->module->getRepository();
        $storage = $repository->getStorage();
        $treeData = $storage->read('tree');

        $getNodeIds = function(array $node) use (&$getNodeIds)
        {
            $ids = array($node['identifier']);
            foreach ($node['children'] as $childNode)
            {
                $ids = array_merge($ids, $getNodeIds($childNode));
            }
            return $ids;
        };

        $ids = array();
        foreach ($treeData['rootNode']['children'] as $topLevelNode)
        {
            $ids = array_merge($ids, $getNodeIds($topLevelNode));
        }

        $query = Elastica\Query::create(NULL);
        $query->setFilter(new Elastica\Filter\Ids(
            $this->module->getOption('prefix'), 
            array_unique($ids)
        ));

        $docMap = array();
        $documents = $repository->find($query, 10000, 0);

        foreach ($documents['documents'] as $document)
        {
            $docMap[$document->getIdentifier()] = $document;
        }

        $createNode = function(array $node) use (&$createNode, &$docMap)
        {
            $document = $docMap[$node['identifier']];
            $children = array();
            foreach ($node['children'] as $childNode)
            {
                $children[] = $createNode($childNode);
            }
            return new Tree\DocumentNode($document, $children);
        };

        $topLevelNodes = array();
        foreach ($treeData['rootNode']['children'] as $topLevelNode)
        {
            $topLevelNodes[] = $createNode($topLevelNode);
        }

        return new Tree\Tree(new Tree\RootNode($topLevelNodes));
    }

    public function storeTree(Tree\Tree $tree)
    {
        // @todo implement
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
