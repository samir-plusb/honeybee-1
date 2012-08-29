<?php

class Events_Export_ExportSuccessView extends EventsBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished exporting ur events.");
    }
}

?>