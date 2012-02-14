<?php

class ImperiaImportTest extends IDataImportBaseTestCase
{
    static private $docIds = array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1389047',
        '/2/10330/10343/10890/1385317',
        '/2/10/65/368/1388875'
    );

    protected function getImportName()
    {
        return 'workflow';
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