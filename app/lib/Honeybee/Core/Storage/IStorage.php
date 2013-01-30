<?php

namespace Honeybee\Core\Storage;

use Honeybee\Core\Dat0r\Document;

interface IStorage
{
    public function read($identifier, $revision = NULL);

    public function writeOne(Document $document);

    public function writeMany(array $documents);
}
