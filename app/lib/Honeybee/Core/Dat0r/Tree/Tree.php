<?php

namespace Honeybee\Core\Dat0r\Tree;

use Honeybee\Core\Dat0r\Module;
use Elastica;

class Tree implements ITree
{
    protected $module;

    protected $rootNode;

    protected $identifier;

    protected $revision;

    public function __construct(Module $module, array $data = array())
    {
        $this->module = $module;
        
        if (! empty($data))
        {
            $this->hydrate($data);
        }
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
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
        return array(
            'rootNode' => $this->rootNode->toArray($level, $expand),
            'identifier' => $this->getIdentifier(),
            'revision' => $this->getRevision()
        );
    }

    public function hydrate(array $treeData)
    {
        $service = $this->module->getService();
        $documents = $service->getMany();

        $documentIdMap = array();
        foreach ($documents['documents'] as $document)
        {
            $documentIdMap[$document->getIdentifier()] = $document;
        }

        $rootChildren = array();

        if (isset($treeData['rootNode']))
        {
            foreach ($treeData['rootNode']['children'] as $topLevelNode)
            {
                $rootChildren[] = $this->createNode($topLevelNode, $documentIdMap);
            }
        }

        foreach ($documentIdMap as $leftOver)
        {
            $rootChildren[] = new DocumentNode($leftOver, array());
        }

        $this->rootNode = new RootNode($rootChildren);
        $this->identifier = $treeData['identifier'];
        $this->revision = isset($treeData['revision']) ? $treeData['revision'] : NULL;

        return $this;
    }

    protected function createNode(array $nodeData, array &$documentIdMap)
    {
        $children = array();
        $document = $documentIdMap[$nodeData['identifier']];

        foreach ($nodeData['children'] as $childNode)
        {
            $children[] = $this->createNode($childNode, $documentIdMap);
        }

        unset($documentIdMap[$nodeData['identifier']]);

        return new DocumentNode($document, $children);
    }
}
