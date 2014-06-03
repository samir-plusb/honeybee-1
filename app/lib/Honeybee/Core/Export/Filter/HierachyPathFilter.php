<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Dat0r\BaseDocument;
use Dat0r\Core\Document as Dat0r;
use Dat0r\Core\Field\ReferenceField;

class HierachyPathFilter extends BaseFilter
{
    protected $tree_cache = array();

    public function execute(BaseDocument $document)
    {
        $filterOutput = array();

        $tree = $this->getTree($document);
        $documentNode = $this->getNodeById(
            $document->getIdentifier(),
            $tree->getRootNode()
        );

        $curNode = $documentNode;
        $pathParts = array($curNode->getDocument()->getShortIdentifier());

        while ($curNode->hasParent())
        {
            $curNode = $curNode->getParent();

            if (! ($curNode instanceof Tree\RootNode))
            {
                $pathParts[] = $curNode->getDocument()->getShortIdentifier();
            }
        }

        $pathParts = array_reverse($pathParts);
        $pathSep = $this->getConfig()->get('path_separator');
        $exportKey = $this->getConfig()->get('export_key');

        return array(
            $exportKey => $pathSep . implode($pathSep, $pathParts)
        );
    }

    protected function getNodeById($nodeId, $node)
    {
        foreach ($node->getChildren() as $childNode)
        {
            if ($nodeId === $childNode->getDocument()->getIdentifier())
            {
                return $childNode;
            }
            else
            {
                if ($foundNode = $this->getNodeById($nodeId, $childNode))
                {
                    return $foundNode;
                }
            }
        }

        return NULL;
    }

    protected function getTree(BaseDocument $document)
    {
        $module = $document->getModule();
        $cache_key = $module->getOption('prefix');

        if (!array_key_exists($cache_key, $this->tree_cache))
        {
            $this->tree_cache[$cache_key] = $document->getModule()->getService('tree')->get();
        }

        return $this->tree_cache[$cache_key];
    }
}
