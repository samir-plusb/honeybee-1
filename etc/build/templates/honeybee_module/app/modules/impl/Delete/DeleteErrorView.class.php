<?php

class %%MODULE_NAME%%_Delete_DeleteErrorView extends %%MODULE_NAME%%BaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'error',
            'errors' => $this->getAttribute('errors', array())
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
