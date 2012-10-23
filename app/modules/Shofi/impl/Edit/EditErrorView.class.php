<?php

class Shofi_Edit_EditErrorView extends ShofiBaseView
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