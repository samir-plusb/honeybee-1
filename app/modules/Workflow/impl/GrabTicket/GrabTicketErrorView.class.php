<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: GrabTicketErrorView.class.php -1   $
 * @package Workflow
 */
class Workflow_GrabTicket_GrabTicketErrorView extends ProjectBaseView
{
    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeText()
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        return $this->getAttribute('error_msg');
    }

    /**
     * (non-PHPdoc)
     * @see ProjectBaseView::executeJson()
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        return json_encode(
            array('resp' => $this->getAttribute('error_msg'))
        );
    }
}

?>
