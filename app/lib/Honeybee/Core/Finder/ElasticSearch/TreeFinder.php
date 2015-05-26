<?php

namespace Honeybee\Core\Finder\ElasticSearch;

use Honeybee\Core\Finder\IFinder;
use Honeybee\Agavi\Database\ElasticSearch\Database;
use Elastica;

class TreeFinder implements IFinder
{
    const ELASTIC_ID = '_id';

    const ELASTIC_REV = '_rev';

    const DOC_IDENTIFIER = 'identifier';

    const DOC_REVISION = 'revision';

    const DOC_IMPLEMENTOR = 'type';

    private $database;

    private $type;

    public function __construct(Database $database, $type = NULL)
    {
        $this->database = $database;
        $this->type = $type;
    }

    public function find($treeName, $limit = 0, $offset = 0)
    {
        $index = $this->getDatabase()->getResource();
        $type = $index->getType('tree');
        $response = $type->request($treeName, 'GET');
        $data = $response->getData();

        return $data['found'] ? $this->mapElasticSearchDataToDomain($data['_source']) : NULL;
    }

    public function fetchAll($limit = 0, $offset = 0)
    {
        // not sure if we really need this.
        // to implement fire a match all query on the tree-type.
        return array();
    }

    protected function getDatabase()
    {
        return $this->database;
    }

    protected function mapElasticSearchDataToDomain(array $data)
    {
        $docType = isset($data[self::DOC_IMPLEMENTOR]) ? $data[self::DOC_IMPLEMENTOR] : FALSE;

        if (! $docType || ! class_exists($docType, TRUE))
        {
            throw new \Exception(
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
