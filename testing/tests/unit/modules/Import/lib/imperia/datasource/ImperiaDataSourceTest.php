<?php

class ImperiaDataSourceTest extends DataSourceBaseTestCase
{
    const CFG_CONFIG_FIXTURE = 'import/config/config.datasource.php';

    static private $docIds = array( // normally these are provided by the imperia import trigger
        '/2/10330/10343/10890/1389367',    // without media !!!
        '/2/10330/10343/10890/1385317',
        '/2/10/65/368/1388875'
    );

    protected function getDataSourceClass()
    {
        return 'ImperiaDataSource';
    }

    protected function getDataSourceName()
    {
        return 'imperia';
    }

    protected function getExpectedLoopCount()
    {
        return 3;
    }

    protected function getExpectedRecordType()
    {
        return 'PoliceReportDataRecord';
    }

    protected function getDataSourceParameters()
    {
        return array(
            ImperiaDataSourceConfig::CFG_DOCIDS => self::$docIds
        );
    }

    protected function getDataSourceDescription()
    {
        return 'Provides imperia export xml data.';
    }
}

?>