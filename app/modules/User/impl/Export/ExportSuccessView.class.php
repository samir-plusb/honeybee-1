<?php

class User_Export_ExportSuccessView extends UserBaseView
{
    public function executeConsole(\AgaviRequestDataHolder $request_data)
    {
        return "Finished exporting User documents." . PHP_EOL;
    }
}
