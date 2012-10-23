<?php

class Shofi_FixData_FixDataSuccessView extends ShofiBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished fixing ur shofi-places.");
    }
}

?>