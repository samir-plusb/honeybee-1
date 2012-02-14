<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id: ProceedSuccessView.class.php 679 2012-01-09 17:23:50Z tschmitt $
 * @package Workflow
 */
class Workflow_Proceed_ProceedSuccessView extends ProjectBaseView
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
