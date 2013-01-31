<?php

namespace Honeybee\Core\Dat0r\Tree;

use Honeybee\Core\Dat0r\Module;
use Elastica;

class Tree implements ITree
{
    protected $rootNode;

    protected $module;

    protected $name;

    public function __construct(Module $module, $name, RootNode $rootNode = NULL)
    {
        $this->rootNode = $rootNode;
        $this->module = $module;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getRootNode()
    {
        return $this->rootNode;
    }

    public function toArray($level = NULL, $expand = TRUE)
    {
        $treeData = array(
            'rootNode' => $this->rootNode->toArray($level, $expand)
        );

        if (TRUE === $expand)
        {
            $treeData['name'] = $this->getName();
        }

        return $treeData;
    }

    public function hydrate(array $treeData)
    {
        $repository = $this->module->getRepository();
        $storage = $repository->getStorage();
        // load all ids from tree, in order to fetch their documents...
        $ids = array();
        $getNodeIds = function(array $node) use (&$getNodeIds)
        {
            $ids = array($node['identifier']);
            foreach ($node['children'] as $childNode)
            {
                $ids = array_merge($ids, $getNodeIds($childNode));
            }
            return $ids;
        };
        foreach ($treeData['rootNode']['children'] as $topLevelNode)
        {
            $ids = array_merge($ids, $getNodeIds($topLevelNode));
        }
        // ... then fetch documents from index
        $query = Elastica\Query::create(NULL);
        $query->setFilter(new Elastica\Filter\Ids(
            $this->module->getOption('prefix'), 
            array_unique($ids)
        ));
        $documents = $repository->find($query, 10000, 0);
        // make documents from collection accessable bei identifier ...
        $docMap = array();
        foreach ($documents['documents'] as $document)
        {
            $docMap[$document->getIdentifier()] = $document;
        }
        // ... then create the actual document tree.
        $topLevelNodes = array();
        $createNode = function(array $node) use (&$createNode, &$docMap)
        {
            $document = $docMap[$node['identifier']];
            $children = array();
            foreach ($node['children'] as $childNode)
            {
                $children[] = $createNode($childNode);
            }
            return new DocumentNode($document, $children);
        };
        foreach ($treeData['rootNode']['children'] as $topLevelNode)
        {
            $topLevelNodes[] = $createNode($topLevelNode);
        }

        $this->rootNode = new RootNode($topLevelNodes);

        return $this;
    }
}
