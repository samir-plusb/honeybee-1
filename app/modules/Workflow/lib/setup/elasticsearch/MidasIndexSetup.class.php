<?php

class MidasIndexSetup implements IDatabaseSetup
{
    /**
     *
     * @var ElasticSearchDatabase
     */
    protected $database;

    protected $indexDefPath;

    public function __construct(ElasticSearchDatabase $database, $indexDefPath = NULL)
    {
        $this->database = $database;
        $this->indexDefPath = $indexDefPath ? $indexDefPath : dirname(__FILE__) . '/index.json';
    }

    public function setup($tearDownFirst = FALSE)
    {
        $indexSettings = json_decode(
            file_get_contents($this->indexDefPath),
            TRUE
        );
        $index = $this->database->getConnection();
        if ($tearDownFirst)
        {
            try
            {
                $index->delete();
            }
            catch(Exception $e)
            {
                // log and continue.
            }
        }
        $index->create($indexSettings, $tearDownFirst);
        if ($this->database->hasParameter('river'))
        {
            $riverParams = $this->database->getParameter('river');
            $riverPath = sprintf(
                "_river/%s/_meta",
                $riverParams['name']
            );
            $riverFile = sprintf("%s/%s.json",
                dirname(__FILE__),
                $riverParams['config']
            );
            $riverSettings = json_decode(
                file_get_contents($riverFile),
                TRUE
            );
            $riverSettings['couchdb']['db'] = $riverParams['db'];
            $riverSettings['index']['index'] = $this->database->getParameter('index');

            $index->getClient()->request($riverPath, 'DELETE');
            $index->getClient()->request($riverPath, 'PUT', $riverSettings);
        }
    }
}

?>
