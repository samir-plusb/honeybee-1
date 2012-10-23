<?php

class Shofi_Verticals_Edit_EditErrorView extends ShofiVerticalsBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'error',
            'data' => array(
                'errors' => $this->getAttribute('errors', array())
            )
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>