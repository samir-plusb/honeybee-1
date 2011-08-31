<?php

interface IDataRecord
{
    /**
     * @param string $fieldname
     * @param mixed $default
     */
    public function getValue($fieldname, $default);

    /**
     * return array<string>
     */
    public function getSupportedFields();
}

?>
