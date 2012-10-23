<?php

class Events_Edit_EditSuccessView extends EventsBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'ok',
            'messages' => array('Event-Daten wurden gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'ticket_id' => $this->getAttribute('ticket_id') 
            )
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}
