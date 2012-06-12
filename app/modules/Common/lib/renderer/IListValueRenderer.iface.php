<?php

interface IListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array());

    public function renderTemplate(ListField $field, $options = array());
}

?>