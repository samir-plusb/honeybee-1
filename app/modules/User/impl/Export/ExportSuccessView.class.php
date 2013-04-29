<?php

class User_Export_ExportSuccessView extends UserBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished exporting ur User documents.");
    }
}
