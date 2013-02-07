<?php

namespace Honeybee\Core\Storage;

use Honeybee\Core\Dat0r\Document;

interface IStorage
{
    public function read($identifier, $revision = NULL);

    public function writeOne($data);

    public function writeMany(array $bulkData);

    public function delete($identifier, $revision = NULL);
}
