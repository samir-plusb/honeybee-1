<?php

abstract class ImportBaseDataRecord implements IDataRecord, IComparable
{
    protected $identifier;
    
    protected $data;

    abstract protected function parse($dataSrc);
    
    abstract protected function getIdentifierFieldName();

    public function __construct($dataSrc)
    {
        $this->data = $this->parse($dataSrc);
        $identifierFieldname = $this->getIdentifierFieldName();
        
        if (!isset($this->data[$identifierFieldname]))
        {
            throw new DataRecordException(
                "No record identifier given for identifier-field: " . $identifierFieldname
            );
        }
        /**
         * @todo we might want to put this somewhere else so can ovderride the behaviour for generating the identifier.
         */
        $this->identifier = md5($this->data[$identifierFieldname]);
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
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function compareTo($other)
    {
        if (!($other instanceof IDataRecord))
        {
            throw new DataRecordException("Unable to compare non-datarecord type with data-record.");
        }

        foreach ($this->getSupportedFields() as $supportedField)
        {
            if ($this->getValue($supportedField) !== $other->getValue($supportedField))
            {
                return -1;
            }
        }

        return 0;
    }
}

?>