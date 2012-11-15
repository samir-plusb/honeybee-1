<?php

class CouchDbStorage implements IStorage
{
    private $database;

    public function __construct(CouchDbDatabase $database)
    {
        $this->database = $database;
    }

    public function readOne($identifier, $revision = NULL)
    {

    }

    public function readMany(array $identifiers, array $revisions = array())
    {

    }

    public function writeOne(HoneybeeDocument $document)
    {

    }

    public function writeMany(array $documents)
    {

    }

    protected function getDatabase()
    {
        return $this->database;
    }
}
