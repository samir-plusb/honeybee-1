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

    /* ---- TREE RELATED STUFF - this is not the final API, watch out for changes ^^ ---- */ 
    public function getTree($name = 'tree')
    {
        if (! $this->module->isActingAsTree())
        {
            throw new \Exception(sprintf(
                "The module %s is not acting as a tree. Please make sure you have apllied the acts_as_tree option.",
                $this->module->getName()
            ));
        }

        $repository = $this->module->getRepository();
        $storage = $repository->getStorage();

        if (($treeData = $storage->read($name)))
        {
            $tree = new Tree\Tree($this->module, $name);
            $tree->hydrate($treeData);
        }
        else
        {   
            $tree = $this->createNewTree($name);
        }

        return $tree;
    }

    public function createNewTree($name)
    {
        if (! $this->module->isActingAsTree())
        {
            throw new \Exception(sprintf(
                "The module %s is not acting as a tree. Please make sure you have apllied the acts_as_tree option.",
                $this->module->getName()
            ));
        }

        $tree = NULL;
        $documents = $this->module->getRepository()->find(NULL, 10000, 0);

        $children = array();
        foreach ($documents['documents'] as $document)
        {
            $children[] = new Tree\DocumentNode($document);
        }

        return new Tree\Tree($this->module, $name, new Tree\RootNode($children));
    }

    public function storeTree(Tree\Tree $tree)
    {
        if (! $this->module->isActingAsTree())
        {
            throw new \Exception(sprintf(
                "The module %s is not acting as a tree. Please make sure you have apllied the acts_as_tree option.",
                $this->module->getName()
            ));
        }

        $repository = $this->module->getRepository();
        $storage = $repository->getStorage();
        $treeDoc = $storage->read($tree->getName());

        if (! $treeDoc)
        {
            $treeDoc = array(
                '_id' => $tree->getName(),
                'type' => get_class($tree),
                'rootNode' => array('children' => array())
            );
        }

        $newStructure = $tree->toArray(NULL, FALSE);
        $treeDoc['rootNode']['children'] = $newStructure['rootNode']['children'];

        $storage->getDatabase()->getConnection()->storeDoc(NULL, $treeDoc);
    }
    /* ---- end of tree related stuff ---- */

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
