<?php

class ElasticSearchFinder implements IFinder
{
    private $database;

    private $type;

    public function __construct(ElasticSearchDatabase $database, $type = NULL)
    {
        $this->database = $database;
        $this->type = $type;
    }

    public function findOne($query)
    {
        $document = NULL;

        if (! $query instanceof Elastica_Query)
        {
            $query = Elastica_Query::create($query);
        }
        
        $query->setLimit(1);

        $source = $this->getQuerySource();
        $result = $source->search($query);

        // @todo Hydrate result into document.
        return $document;
    }

    public function findMany($query, $limit = 0, $offset = 0)
    {
        if (! $query instanceof Elastica_Query)
        {
            $query = Elastica_Query::create($query);
        }

        // @todo maybe add some more convenience 'round query creation.

        $query->setLimit($limit)->setFrom($offset);

        $source = $this->getQuerySource();
        $result = $source->search($query);

        // @todo Introduce a finder result or document collection...
        return $result;
    }

    public function findAll($limit = 0, $offset = 0)
    {
        $query = Elastica_Query::create(NULL);
        $query->setLimit($limit)->setFrom($offset);

        $source = $this->getQuerySource();
        $result = $source->search($query);

        // @todo Introduce a finder result or document collection...
        return $result;
    }

    protected function getDatabase()
    {
        return $this->database;
    }

    protected function getQuerySource()
    {
        $index = $this->getDatabase()->getResource();

        return ($this->type) ? $index->getType($this->type) : $index;
    }
}
