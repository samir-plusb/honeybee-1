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
            array('state' => 'ok')
        );
    }
}

?>
