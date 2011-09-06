<?php

interface IDataRecord
{
    /**
     * @param string $fieldname
     * @param mixed $default
     */
    public function getValue($fieldname, $default = NULL);

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