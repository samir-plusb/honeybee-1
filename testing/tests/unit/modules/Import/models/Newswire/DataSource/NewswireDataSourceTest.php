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
class NewswireDataSourceTest extends AgaviUnitTestCase
{
    const CFG_CONFIG = 'configs/imports/newswire-dpa.xml';

    const CFG_XML_FIXTURE = 'data/import/imperia/polizeimeldung.article.xml';

    /**
     * @var NewswireDataSource
     */
    protected $dataSource;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dataSource = new NewswireDataSource($this->createDataSourceConfig());
        $this->dataSource->resetTimestamp();
    }


    /**
     *
     * Enter description here ...
     */
    public function testNextRecordCount()
    {
        $count = 0;
        while ($this->dataSource->nextRecord())
        {
            $count ++;
        }
        $this->assertEquals(5, $count);
    }

    /**
     *
     * Enter description here ...
     */
    public function testNextRecord()
    {
        for ($i = 0; $i < 5; $i++)
        {
            $record = $this->dataSource->nextRecord();
            $this->assertInstanceOf('DpaNitfNewswireDataRecord', $record);
        }
    }

    /**
     *
     * Enter description here ...
     * @return
     */
    protected function createDataSourceConfig()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $factoryConfigFile = $baseDir . self::CFG_CONFIG;
        $config = new DataImportFactoryConfig($factoryConfigFile);

        $dataSrcSettings = $config->getSetting(DataImportFactoryConfig::CFG_DATASRC);

        $dataSrcSettings = array_merge(
            $dataSrcSettings['settings'],
            array(
                NewswireDataSourceConfig::CFG_RECORD_TYPE =>  $dataSrcSettings['record'],
                NewswireDataSourceConfig::CFG_GLOB => $baseDir . $dataSrcSettings['settings'][NewswireDataSourceConfig::CFG_GLOB],
            )
        );

        return new NewswireDataSourceConfig($dataSrcSettings);
    }

}

?>
