<?php

class %%MODULE_NAME%%_Delete_DeleteSuccessView extends %%MODULE_NAME%%BaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'ok',
            'messages' => array('Das Dokument wurde erfolgreich gelöscht.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array()
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
