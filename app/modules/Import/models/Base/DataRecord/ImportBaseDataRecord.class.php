<?php

abstract class ImportBaseDataRecord implements IDataRecord
{
    protected $data;
    
    abstract protected function parse($dataSrc);
    
    public function __construct($dataSrc)
    {
        $this->data = $this->parse($dataSrc);
    }
    
    public function getSupportedFields()
    {
        return array_keys($this->data);
    }
    
    public function getValue($fieldname, $default = null)
    {
        $value = $default;
        
        if (isset($this->data[$fieldname]))
        {
            $value = $this->data[$fieldname];
        }
        
        return $value;
    }
}

?>
