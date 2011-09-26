<?php

class ImperiaImportTest extends CouchDbDataImportBaseTestCase
{
    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    );

    protected function getImportName()
    {
        return 'couchdb';
    }

    // @codeCoverageIgnoreStart

    protected function getDataSourceNames()
    {
        return array('imperia');
    }

    protected function getDataSourceParameters($dataSourceName)
    {
        return array(
            ImperiaDataSourceConfig::CFG_DOCIDS => self::$docIds
        );
    }

    // @codeCoverageIgnoreEnd
}

?>