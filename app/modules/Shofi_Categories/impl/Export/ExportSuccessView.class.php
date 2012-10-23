<?php

class Shofi_Categories_Export_ExportSuccessView extends ShofiBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished exporting ur shofi-categories.");
    }
}

?>