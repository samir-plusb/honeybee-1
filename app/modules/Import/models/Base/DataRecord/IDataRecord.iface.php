<?php

interface IDataRecord
{
    /**
     * @param string $fieldname
     * @param mixed $default
     */
    public function getValue($fieldname, $default = null);

    /**
     * return array<string>
     */
    public function getSupportedFields();
}

?>
