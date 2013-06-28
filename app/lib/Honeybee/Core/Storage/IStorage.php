<?php

namespace Honeybee\Core\Storage;

use Honeybee\Core\Dat0r\Document;

interface IStorage
{
    public function write(array $data);
    
    /**
     * @todo Storages should not read at all!
     */
    public function read($identifier, $revision = NULL);

    public function delete($identifier, $revision = NULL);
}
