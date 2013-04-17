<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Tree;
use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Runtime\Document as Dat0r;
use Dat0r\Core\Runtime\Field\ReferenceField;

class HierachyPathFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $filterOutput = array();

        $tree = $document->getModule()->getService('tree')->get();
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
        array_shift($pathParts);
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
}
