<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Workflow
 * @subpackage Mvc
 */
class Workflow_ReleaseTicket_ReleaseTicketSuccessView extends WorkflowBaseView
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
