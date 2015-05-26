<?php

namespace Honeybee\Agavi\Database\ElasticSearch;

use Honeybee\Agavi\Database\IDatabaseSetup;
use Elastica;

class DatabaseSetup implements IDatabaseSetup
{
    const DEFAULT_COUCH_HOST = 'localhost';

    const DEFAULT_COUCH_PORT = 5984;

    const DEFAULT_BULK_SIZE = 100;

    const DEFAULT_BULK_TIMEOUT = '10ms';

    /**
     * @var Honeybee\Agavi\Database\ElasticSearch\Database $database
     */
    protected $database;

    public function execute(\AgaviDatabase $database, $tearDownFirst = FALSE)
    {
        $this->database = $database;

        $this->createIndex($tearDownFirst);
        $this->registerMappings();
        $this->initializeRiver();
    }

    public function tearDown()
    {
        $this->deleteRiver();
        $this->database->getResource()->delete();
    }

    protected function createIndex($tearDownFirst)
    {
        // Delete before the index to prevent a current river from crashing
        if (TRUE === $tearDownFirst)
        {
            $this->deleteRiver();
        }

        $indexSettings = array(
            'number_of_shards' => 2,
            'number_of_replicas' => 1,
            'analysis' => array(
                'analyzer' => array(
                    "DefaultAnalyzer" => array(
                        'type' => 'custom',
                        'tokenizer' => 'whitespace',
                        'filter' => array('lowercase')
                    ),
                    "AutoCompleteAnalyzer" => array(
                      "type" => "custom",
                      "tokenizer" => "whitespace",
                      "filter" => array("lowercase", "edge")
                    ),
                    'IcuAnalyzer_DE' => array(
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array('collation_de')
                    )
                ),
                'filter' => array(
                    'snowball' => array(
                        'type' => 'snowball',
                        'language' => 'German2'
                    ),
                    'edge' => array(
                      'type' => 'edgeNGram',
                      'min_gram' => 1,
                      'max_gram' => 10,
                      'side' => 'front'
                    ),
                    'collation_de' => array(
                        'type' => "icu_collation",
                        'language' => "de", // german
                        'country' => "DE", // Germany
                        //'variant' : "MV", // hahaha Mecklenburg-Vorpommern :-D
                        //'strength' => "quaternary", // "tertiary" is the usual default
                        //'decomposition' => "canonical", // "no" is faster, as no normalization takes place and many languages don't require text normalization
                        'alternate' => "shifted", // or "non-ignorable", sets alternate handling for "quaternary" strength; ignoring punctuation and whitespace
                        'caseLevel' => true, // ignore accent differences in "primary" strength?; defaults to "false"
                        'caseFirst' => "upper", // or "upper", which case is sorted first in "tertiary" strength?
                        'numeric' => true, // defaults to false; should "egg-9" be before "egg-21"?
                        'hiraganaQuaternaryMode' => false // distinguish between Katakana and Hiragana characters in "quaternary" strength
                    )
                )
            )
        );

        $this->database->getResource()->create($indexSettings, $tearDownFirst);
    }

    protected function registerMappings()
    {
        $mappingDirectory = $this->database->getParameter('mapping_dir');
        if (! is_dir($mappingDirectory))
        {
            throw new \AgaviDatabaseException(
                "Unable to find configured mapping directory: $mappingDirectory"
            );
        }

        $mappingFiles = glob(sprintf('%s/*.mapping.json', $mappingDirectory));
        foreach ($mappingFiles as $mappingFile)
        {
            $mappingDef = json_decode(file_get_contents($mappingFile), TRUE);
            $index = $this->database->getResource();
            $elasticaType = $index->gettype(str_replace('.mapping.json', '', basename($mappingFile)));

            $mapping = new Elastica\Type\Mapping();
            $mapping->setType($elasticaType);

            foreach ($mappingDef as $prop => $value)
            {
                if ('properties' === $prop)
                {
                    continue;
                }
                $mapping->setParam($prop, $value);
            }

            $mapping->setProperties($mappingDef['properties']);
            $mapping->send();
        }
    }

    protected function initializeRiver()
    {
        $mappingDirectory = $this->database->getParameter('mapping_dir');
        if (! is_dir($mappingDirectory))
        {
            throw new \AgaviDatabaseException(
                "Unable to find configured mapping directory: $mappingDirectory"
            );
        }

        $riverDb = $this->database->getParameter('river[couch_db]');
        if (! is_dir($mappingDirectory))
        {
            throw new \AgaviDatabaseException(
                "Unable to find configured mapping directory: $mappingDirectory"
            );
        }

        $riverFileGlob = sprintf('%s/*.river.js', $mappingDirectory);
        $riverFiles = glob($riverFileGlob);
        if (1 !== count($riverFiles))
        {
            throw new \AgaviDatabaseException("Only one river definition per module allowed.");
        }
        $riverScriptFile = $riverFiles[0];
        $typeName = str_replace('.river.js', '', basename($riverScriptFile));

        $riverSettings = array(
            'type' => 'couchdb',
            'couchdb' => array(
                'db' => $this->database->getParameter('river[couch_db]'),
                'host' => $this->database->getParameter('river[couch_host]', self::DEFAULT_COUCH_HOST),
                'port' => $this->database->getParameter('river[couch_port]', self::DEFAULT_COUCH_PORT),
                'filter' => '', // @todo implement when needed
                'script_type' => 'javascript',
                'script' => $this->reformatJavascript(
                    file_get_contents($riverScriptFile)
                )
            ),
            'index' => array(
                'index' => $this->database->getResource()->getName(),
                'bulk_size' => $this->database->getParameter('river[bulk_size]', self::DEFAULT_BULK_SIZE),
                'bulk_timeout' => $this->database->getParameter('river[bulk_timeout]', self::DEFAULT_BULK_TIMEOUT)
            )
        );

        $riverName = sprintf('river_%s', $this->database->getResource()->getName());
        $this->database->getConnection()->request(
            sprintf("_river/%s/_meta", $riverName),
            Elastica\Request::PUT,
            $riverSettings
        );
    }

    protected function deleteRiver($name)
    {
        $mappingDirectory = $this->database->getParameter('mapping_dir');
        if (! is_dir($mappingDirectory))
        {
            throw new \AgaviDatabaseException(
                "Unable to find configured mapping directory: $mappingDirectory"
            );
        }

        $riverFileGlob = sprintf('%s/*.river.js', $mappingDirectory);
        $riverFiles = glob($riverFileGlob);
        if (1 !== count($riverFiles))
        {
            throw new \AgaviDatabaseException("Only one river definition per module allowed.");
        }

        $riverScriptFile = $riverFiles[0];
        $typeName = str_replace('.river.js', '', basename($riverScriptFile));

        $this->database->getConnection()->request(
            sprintf("_river/%s", $typeName),
            Elastica\Request::DELETE
        );
    }

    protected function reformatJavascript($jsString)
    {
        // strip /* … */ comments
        $jsString = preg_replace('#/\*.*?\*/#s', ' ', $jsString);
        // strip // … comments
        $jsString = preg_replace('#\s//\s.*#', ' ', $jsString);
        // strip multiple white spaces
        $jsString = preg_replace('/\s+/s', ' ', $jsString);

        return trim($jsString);
    }
}
