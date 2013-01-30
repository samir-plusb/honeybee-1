<?php

class TicketOwnerListValueRenderer extends DefaultListValueRenderer
{
    protected function getTemplateDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    protected function getTemplatePath()
    {
        return 'WorkflowState.tpl.twig';

    }
}

?>
