<?php
/**
 * Test the newswire datasource
 *
 * @package Import
 * @subpackage Test
 * @author tay
 * @version $Id: NewswireDataSourceTest.php -1   $
 *
 */
class NewswireDataSourceTest extends DataSourceBaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->dataSource->resetTimestamp();
    }

    protected function getDataSourceClass()
    {
        return 'NewswireDataSource';
    }

    protected function getDataSourceName()
    {
        return 'dpa';
    }

    protected function getExpectedRecordType()
    {
        return 'DpaNitfNewswireDataRecord';
    }

    protected function getExpectedLoopCount()
    {
        return 5;
    }

    protected function getDataSourceDescription()
    {
        return 'Foo the bar had a very fuzen buzen (Newswire).';
    }
}

?>