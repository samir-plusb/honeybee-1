<?php

namespace Honeybee\Core\Storage;

use Honeybee\Core\Dat0r\Document;

interface IStorage
{
    public function write(array $data);
    
    public function read($identifier, $revision = NULL);

    public function delete($identifier, $revision = NULL);
}
