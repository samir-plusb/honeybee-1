<?php

class User_Export_ExportSuccessView extends UserBaseView
{
    public function executeConsole(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished exporting User documents.");
    }
}
