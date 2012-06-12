<?php

class Shofi_Edit_EditSuccessView extends ShofiBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $page = $parameters->getParameter('_page', 'CoreItem');
        $data = array(
            'state' => 'ok',
            'messages' => array($page . '-Daten wurden gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'ticket_id' => $this->getAttribute('ticket_id') 
            )
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}

?>