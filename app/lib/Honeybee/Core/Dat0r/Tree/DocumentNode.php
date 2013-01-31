<?php

namespace Honeybee\Core\Dat0r\Tree;

use Honeybee\Core\Dat0r\Document;

class DocumentNode extends Node
{
    protected $document;

    public function __construct(Document $document, array $children = array())
    {
        $this->document = $document;

        parent::__construct($children);
    }

    public function getIdentifier()
    {
        return $this->document->getIdentifier();
    }

    public function getLabel()
    {
        return $this->document->getName();
    }

    public function getDocument()
    {
        return $this->document;
    }
}
