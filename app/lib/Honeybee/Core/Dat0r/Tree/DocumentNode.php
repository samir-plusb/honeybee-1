<?php

namespace Honeybee\Core\Dat0r\Tree;

use Honeybee\Core\Dat0r\Document;

class DocumentNode extends Node
{
    protected $document;

    protected $labelFieldname;

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
        return $this->document->getValue(
            $this->document->getModule()->getOption('tree_label_field')
        );
    }

    public function getDocument()
    {
        return $this->document;
    }
}
