<?php

use Dat0r\Core\Document\IDocument;

class EmailFieldInputRenderer extends FieldInputRenderer
{
    protected function getTemplateName()
    {
        return "Email.tpl.twig";
    }
}
