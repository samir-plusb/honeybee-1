<?php

class MidasIndexSetup implements IDatabaseSetup
{
    /**
     * @var ElasticSearchDatabase
     */
    protected $database;

    public function __construct()
    {
        $this->database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase('EsNews');
    }

    public function setup($tearDownFirst = FALSE)
    {
        // Delete before the index to prevent a current river from crashing
        if (TRUE === $tearDownFirst)
        {
            $this->deleteRiver();
        }

        $this->createIndex($tearDownFirst);

        if ($this->database->hasParameter('river'))
        {
            $this->createRiver();
        }
    }

    public function tearDown()
    {
        $this->deleteRiver();
        $this->database->getResource()->delete();
    }

    protected function createIndex($tearDownFirst)
    {
        $indexSettings = json_decode(
            file_get_contents(dirname(__FILE__) . '/index.json'),
            TRUE
        );
        $index = $this->database->getResource();
        $index->create($indexSettings, $tearDownFirst);
    }

    protected function createRiver()
    {
        $riverParams = $this->database->getParameter('river');
        $riverPath = sprintf(
            "_river/%s/_meta",
            $riverParams['name']
        );
        $riverSettings = json_decode(
            file_get_contents(
                sprintf("%s/%s.json", dirname(__FILE__), $riverParams['config'])
            ),
            TRUE
        );
        $riverSettings['couchdb']['db'] = $riverParams['db'];
        $riverSettings['index']['index'] = $this->database->getParameter('index');

        $this->database->getConnection()->request($riverPath, Elastica_Request::PUT, $riverSettings);
    }

    protected function deleteRiver()
    {
        $riverParams = $this->database->getParameter('river');
        $riverPath = sprintf(
            "_river/%s",
            $riverParams['name']
        );

        $this->database->getConnection()->request($riverPath, Elastica_Request::DELETE);
    }
}

?>
