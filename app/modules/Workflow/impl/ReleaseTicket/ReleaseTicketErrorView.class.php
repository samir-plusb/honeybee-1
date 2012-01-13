<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: GrabTicketErrorView.class.php -1   $
 * @package Workflow
 */
class Workflow_ReleaseTicket_ReleaseTicketErrorView extends ProjectBaseView
{
    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        return json_encode(
            array(
                'state' => 'error',
                'reason' => $this->getAttribute('reason'),
                'msg' => $this->getAttribute('error_msg')
            )
        );
    }
}

?>
