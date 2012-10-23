<?php

class Movies_Export_ExportSuccessView extends MoviesBaseView
{
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent("Finished exporting ur movies.");
    }
}

?>