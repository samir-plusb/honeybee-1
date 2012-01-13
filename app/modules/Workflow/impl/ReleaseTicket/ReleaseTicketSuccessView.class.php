<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: RunSuccessView.class.php -1   $
 * @package Workflow
 */
class Workflow_ReleaseTicket_ReleaseTicketSuccessView extends ProjectBaseView
{
    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        return json_encode(
            array('state' => 'ok')
        );
    }
}

?>
