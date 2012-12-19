<?php

class Common_Service_DetectFace_Service_DetectFaceSuccessView extends AssetBaseView
{
    public function executeJson(AgaviRequestDataHolder $requestData)
    {
        $this->getResponse()->setContent(
            json_encode(array(
                'aoi' => $this->getAttribute('aoi'),
                'status' => 'ok'
            ))
        );
    }
}
