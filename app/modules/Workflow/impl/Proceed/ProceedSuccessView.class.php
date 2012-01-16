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
