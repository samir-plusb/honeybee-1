<?php

interface IListValueRenderer
{
    public function renderValue($value, $field, array &$data = array());

    public function renderTemplate(ListField $field, $options = array());
}

?>