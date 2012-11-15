<?php

interface IStorage
{
    public function readOne($identifier, $revision = NULL);

    public function readMany(array $identifiers, array $revisions = array());

    public function writeOne(HoneybeeDocument $document);

    public function writeMany(array $documents);
}
