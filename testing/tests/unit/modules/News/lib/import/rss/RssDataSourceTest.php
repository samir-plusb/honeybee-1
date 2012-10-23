<?php

class RssDataSourceTest extends DataSourceBaseTestCase
{
    protected function getDataSourceClass()
    {
        return 'RssDataSource';
    }

    protected function getDataSourceName()
    {
        return 'rss';
    }

    protected function getExpectedRecordType()
    {
        return 'RssDataRecord';
    }

    protected function getExpectedLoopCount()
    {
        return 30;
    }

    protected function getDataSourceDescription()
    {
        return 'Foo the bar had a very fuzen buzen (Rss).';
    }
}

?>