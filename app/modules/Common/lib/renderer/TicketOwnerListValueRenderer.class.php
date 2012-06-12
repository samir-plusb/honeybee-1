<?php

class TicketOwnerListValueRenderer extends DefaultListValueRenderer
{
    protected function getTemplatePath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'WorkflowState.tpl.php';
    }
}

?>