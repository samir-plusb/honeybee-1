<?php

/**
 * The NewsIndexSetup is responseable for setting midas elasticsearch index.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package         Workflow
 * @subpackage      Setup
 */
class NewsIndexSetup implements IDatabaseSetup
{
    /**
     * @var ElasticSearchDatabase
     */
    protected $database;

    public function __construct()
    {
        $this->database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
            NewsFinder::getElasticSearchDatabaseName()
        );
    }

    public function setup($tearDownFirst = FALSE)
    {
        // Delete before the index to prevent a current river from crashing
        if (TRUE === $tearDownFirst)
        {
            $this->deleteRiver();
        }
        $indexDef = $this->database->getParameter('index', array());
        $indexName = isset($indexDef['name']) ? $indexDef['name'] : NULL;
        $this->createIndex($tearDownFirst);

        $types = isset($indexDef['types']) ? $indexDef['types'] : array();
        foreach ($types as $type)
        {
            $this->createMappig($type);
        }

        foreach ($this->database->getParameter('rivers', array()) as $name => $params)
        {
            $this->createRiver($name, $params);
        }
    }

    public function tearDown()
    {
        foreach ($this->database->getParameter('rivers', array()) as $name => $params)
        {
            $this->deleteRiver($name);
        }
        $this->database->getResource()->delete();
    }

    protected function createIndex($tearDownFirst)
    {
        // @todo Make this stuff configurable, hence read/parse definition from an *.index.json
        $this->database->getResource()->create(array(
            'number_of_shards' => 2,
            'number_of_replicas' => 1,
            'analysis' => array(
                'analyzer' => array(
                    'indexAnalyzer' => array(
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => array('lowercase', 'mySnowball')
                    ),
                    'searchAnalyzer' => array(
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => array('standard', 'lowercase', 'mySnowball')
                    )
                ),
                'filter' => array(
                    'mySnowball' => array(
                        'type' => 'snowball',
                        'language' => 'German'
                    )
                )
            )
        ), $tearDownFirst);
    }

    protected function createMappig($type)
    {
        $mappingFile = sprintf('%1$s/%2$s.mapping.json', dirname(__FILE__), $type);
        $typeSettings = json_decode(file_get_contents($mappingFile), TRUE);
        $index = $this->database->getResource();
        $elasticaType = $index->getType($type);
        $mapping = new Elastica_Type_Mapping();
        $mapping->setType($elasticaType);
        foreach ($typeSettings as $prop => $value)
        {
            if ('properties' === $prop)
            {
                continue;
            }
            $mapping->setParam($prop, $value);
        }
        $mapping->setProperties($typeSettings['properties']);
        $mapping->send();
    }

    protected function createRiver($name, array $riverParams)
    {
        $riverPath = sprintf("_river/%s/_meta", $name);
        $riverSettings = json_decode(
            file_get_contents(
                sprintf("%s/%s.json", dirname(__FILE__), $riverParams['config'])
            ),
            TRUE
        );
        $riverSettings['couchdb']['db'] = $riverParams['db'];
        $riverSettings['index']['index'] = $this->database->getResource()->getName();
        $this->database->getConnection()->request($riverPath, Elastica_Request::PUT, $riverSettings);
    }

    protected function deleteRiver($name)
    {
        $riverPath = sprintf("_river/%s", $name);
        $this->database->getConnection()->request($riverPath, Elastica_Request::DELETE);
    }
}

?>
