<?php

/**
 * The ImportBaseDataRecord class is an abstract implementation of the IDataRecord interface.
 * It provides an base implementation of most IDataRecord methods and provides template methods
 * for inheriting classes to hook into normalizing the incoming data on record creation.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/DataRecord
 */
abstract class ImportBaseDataRecord implements IDataRecord, IComparable
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our unique identifier.
     * 
     * @var         string 
     */
    protected $identifier;
    
    /**
     * Holds the data that is exposed by our IDataRecord::getValue() implementation.
     * 
     * @var         array
     */
    protected $data;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------
    
    /**
     * Normaliize the incoming data.
     * This probally the most important method for subclasses to implement,
     * as this where you take your arbitary data(array, file, xml, whatever ...)
     * and bring it into an usable array structure with keys that map to our provided fieldnames.
     * 
     * @param       mixed $data
     * 
     * @return      array
     */
    abstract protected function parse($data);
    
    /**
     * Returns the name of the field to use as the base for building our identifier.
     * 
     * @return      string
     */
    abstract protected function getIdentifierFieldName();
    
    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------
    
    
    // ---------------------------------- <CONSTRCUTOR> ------------------------------------------
    
    /**
     * Create new ImportBaseDataRecord instance, 
     * thereby parsing the given data to setup state.
     * 
     * @param       mixed $data 
     * 
     * @throws      DataRecordException If the identifier field can't be resolved.
     * 
     * @uses        ImportBaseDataRecord::parse()
     * @uses        ImportBaseDataRecord::getIdentifierFieldName()
     */
    public function __construct($data)
    {
        $this->data = $this->parse($data);
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
    
    // ---------------------------------- </CONSTRCUTOR> -----------------------------------------
    
    
    // ---------------------------------- <IDataRecord IMPL> -------------------------------------
    
    /**
     * Return an array of fieldnames that are supported by this record.
     * 
     * @return      array
     * 
     * @see         IDataRecord::getSupportedFields()
     */
    public function getSupportedFields()
    {
        return array_keys($this->data);
    }
    
    /**
     * Return the value for the field represented by the given fieldname.
     * 
     * @param       string $fieldname
     * @param       mixed $default
     * 
     * @return      mixed Returns the given field's value or $default if the field is not set.
     * 
     * @see         IDataRecord::getValue()
     */
    public function getValue($fieldname, $default = NULL)
    {
        $value = $default;

        if (isset($this->data[$fieldname]))
        {
            $value = $this->data[$fieldname];
        }

        return $value;
    }
    
    /**
     * Return an array representation of the given record.
     * 
     * @return      array
     * 
     * @see         IDataRecord::toArray()
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Return an unique identifier that represents this record.
     * 
     * @return      string
     * 
     * @see         IDataRecord::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    // ---------------------------------- </IDataRecord IMPL> ------------------------------------
    
    
    // ---------------------------------- <ICompareable IMPL> ------------------------------------
    
    /**
     * Compares ourself to a given $other IDataRecord.
     * 
     * @param       IDataRecord $other
     * 
     * @return      int Returns -1 if $other is smaller, 0 if equal and 1 if $other is greater. 
     * 
     * @throws      DataRecordException If the given $other is no instance of IDataRecord.
     * 
     * @uses        ImportBaseDataRecord::getSupportedFields()
     * @uses        ImportBaseDataRecord::getValue()
     */
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
    
    // ---------------------------------- </ICompareable IMPL> -----------------------------------
}

?>