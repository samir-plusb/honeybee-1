<?php

class KoReadOnlyFieldInputRenderer extends FieldInputRenderer
{
    protected function getTemplateName()
    {
        return 'KoReadOnlyField.tpl.twig';
    }
}
