<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Mvc
 */
class Shofi_Verticals_Edit_EditSuccessView extends ShofiVerticalsBaseView
{
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'ok',
            'messages' => array('Leuchtturm-Daten wurden gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'ticket_id' => $this->getAttribute('ticket_id') 
            )
        );
        $this->getResponse()->setContent(json_encode($data));
    }
}
