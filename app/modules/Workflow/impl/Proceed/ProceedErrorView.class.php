<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: GrabTicketErrorView.class.php -1   $
 * @package Workflow
 */
class Workflow_Proceed_ProceedErrorView extends ProjectBaseView
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
                'msg' => $this->getAttribute('content')
            )
        );
    }
}

?>
