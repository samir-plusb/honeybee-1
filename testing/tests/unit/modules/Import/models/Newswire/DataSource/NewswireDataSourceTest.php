<?php
/**
 * Test the newswire datasource
 *
 * @package Import
 * @subpackage Test
 * @author tay
 * @version $Id$
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
    
    protected function getDataSourceParameters()
    {
        return array(
            NewswireDataSourceConfig::CFG_GLOB => $this->getGlobSetting()
        );
    }

    protected function getGlobSetting()
    {
        $config = new ImportFactoryConfig(
            AgaviConfig::get('import.config_dir')
        );

        $dataSourceConfig = $config->getDataSourceConfig('dpa');
        $settings = $dataSourceConfig[DataSourcesFactoryConfig::CFG_SETTINGS];
        
        return AgaviConfig::get('core.newswire_dir') . $settings[NewswireDataSourceConfig::CFG_GLOB];
    }
}

?>