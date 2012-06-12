<?php

class Asset_Update_UpdateSuccessView extends AssetBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->getResponse()->setContent(json_encode(array(
            'state' => 'ok',
            'data' => array(
                'asset' => $parameters->getParameter('asset')->toArray()
            ),
            'messages' => array(
                'Dateidaten wurden erfolgreich aktualisiert.'
            )
        )));
    }
}

?>
