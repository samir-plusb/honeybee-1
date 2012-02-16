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
        $this->createIndex($tearDownFirst);

        if ($this->database->hasParameter('river'))
        {
            $this->createRiver($tearDownFirst);
        }
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

    protected function createRiver($tearDownFirst)
    {
        $riverParams = $this->database->getParameter('river');
        $riverPath = sprintf(
            "_river/%s/_meta",
            $riverParams['name']
        );

        if ($tearDownFirst)
        {
            $this->database->getConnection()->request($riverPath, 'DELETE');
        }

        $riverSettings = json_decode(
            file_get_contents(
                sprintf("%s/%s.json", dirname(__FILE__), $riverParams['config'])
            ),
            TRUE
        );
        $riverSettings['couchdb']['db'] = $riverParams['db'];
        $riverSettings['index']['index'] = $this->database->getParameter('index');

        $this->database->getConnection()->request($riverPath, 'PUT', $riverSettings);
    }
}

?>
