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
    
    /**
     * @return array<string, mixed>
     */
    public function toArray();
    
    /**
     * @return string
     */
    public function getIdentifier();
}

?>