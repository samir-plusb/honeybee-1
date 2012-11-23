<?php

class ElasticSearchFinder implements IFinder
{
    const ELASTIC_ID = '_id';

    const ELASTIC_REV = '_rev';

    const DOC_IDENTIFIER = 'identifier';

    const DOC_REVISION = 'revision';

    const DOC_IMPLEMENTOR = 'type';

    private $database;

    private $type;

    public function __construct(ElasticSearchDatabase $database, $type = NULL)
    {
        $this->database = $database;
        $this->type = $type;
    }

    public function find($query, $limit = 0, $offset = 0)
    {
        if (! $query instanceof Elastica_Query)
        {
            $query = Elastica_Query::create($query);
        }

        $query->setLimit($limit)->setFrom($offset);

        $source = $this->getQuerySource();
        $resultSet = $source->search($query);
        
        return $this->convertResultSetToArray($resultSet);
    }

    public function fetchAll($limit = 0, $offset = 0)
    {
        $query = Elastica_Query::create(NULL);
        $query->setLimit($limit)->setFrom($offset);

        $source = $this->getQuerySource();
        $resultSet = $source->search($query);

        return $this->convertResultSetToArray($resultSet);
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

    protected function convertResultSetToArray(Elastica_ResultSet $resultSet)
    {
        $data = array();

        foreach ($resultSet->getResults() as $result)
        {
            $hit = $result->getHit();
            $data[] = $this->mapElasticSearchDataToDomain($hit['_source']);
        }

        return array(
            'data' => $data,
            'totalCount' => $resultSet->getTotalHits()
        );
    }

    protected function mapElasticSearchDataToDomain(array $data)
    {
        $docType = isset($data[self::DOC_IMPLEMENTOR]) ? $data[self::DOC_IMPLEMENTOR] : FALSE;

        if (! $docType || ! class_exists($docType, TRUE))
        {
            throw new Exception(
                "Invalid or corrupt type information within document data."
            );
        }

        unset($data[self::DOC_IMPLEMENTOR]);

        if (isset($data[self::ELASTIC_ID]))
        {
            $data[self::DOC_IDENTIFIER] = $data[self::ELASTIC_ID];
            unset($data[self::ELASTIC_ID]);
        }

        if (isset($data[self::ELASTIC_REV]))
        {
            $data[self::DOC_REVISION] = $data[self::ELASTIC_REV];
            unset($data[self::ELASTIC_REV]);
        }

        return $data;
    }
}
