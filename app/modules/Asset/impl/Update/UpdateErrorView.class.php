<?php

class Asset_Update_UpdateErrorView extends AssetBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent(json_encode(array(
            'state' => 'error',
            'errors' => array(),
            'data' => array()
        )));
    }
}

?>
