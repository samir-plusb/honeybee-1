<?php

class Shofi_FixData_FixDataErrorView extends ShofiBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Failed to fix ur shofi-places.");
    }
}

?>