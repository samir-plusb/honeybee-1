<?php

class Shofi_Categories_Edit_EditSuccessView extends ShofiCategoriesBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'ok',
            'messages' => array('Branchen-Daten wurden gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'ticket_id' => $this->getAttribute('ticket_id') 
            )
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}
