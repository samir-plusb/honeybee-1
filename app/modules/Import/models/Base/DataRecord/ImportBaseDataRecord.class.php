<?php

abstract class ImportBaseDataRecord implements IDataRecord, IComparable
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
