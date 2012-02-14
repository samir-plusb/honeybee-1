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
     * Prepares and sets our json data on our webresponse.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
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
