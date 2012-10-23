<?php

class ContentMachineHttpExportTest extends AgaviPhpUnitTestCase
{
    protected $shofiItem;

    protected $cmExport;

    public function setUp()
    {
        parent::setUp();

        $this->cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );
    }

    public function testExportPlace()
    {
        $fixturePath = AgaviConfig::get('core.fixtures_dir') . DIRECTORY_SEPARATOR . 'Shofi/shofi.item.json';
        $fixtureData = json_decode(file_get_contents($fixturePath), TRUE);
        $shofiItem = ShofiWorkflowItem::fromArray($fixtureData);

        $this->assertTrue($this->cmExport->exportShofiPlace($shofiItem));
    }

    public function testExportCategory()
    {
        $fixturePath = AgaviConfig::get('core.fixtures_dir') . DIRECTORY_SEPARATOR . 'Shofi_Categories/shofi.category.json';
        $fixtureData = json_decode(file_get_contents($fixturePath), TRUE);
        $shofiCategory = ShofiCategoriesWorkflowItem::fromArray($fixtureData);
        
        $this->assertTrue($this->cmExport->exportShofiCategory($shofiCategory));
    }
}

?>
