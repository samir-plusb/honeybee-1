<?php

class WkgDataSourceTest extends DataSourceBaseTestCase
{
    protected function getDataSourceClass()
    {
        return 'WkgDataSource';
    }

    protected function getDataSourceName()
    {
        return 'wkg';
    }

    protected function getExpectedLoopCount()
    {
        return 8;
    }

    protected function getExpectedRecordType()
    {
        return 'WkgDataRecord';
    }

    protected function getDataSourceParameters()
    {
        return array();
    }

    protected function getDataSourceDescription()
    {
        return 'Provides wkg data based on xml files.';
    }
}

?>