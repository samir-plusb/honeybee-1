<?php

interface IStorage
{
    public function read($identifier, $revision = NULL);

    public function writeOne(HoneybeeDocument $document);

    public function writeMany(array $documents);
}
