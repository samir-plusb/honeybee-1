<?php

/**
 * IDataRecord implementations are responseable for normalizing and then transporting
 * data that represents a single data record
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
interface IDataRecord
{
    /**
     * Return the value for the given field.
     * 
     * @param       string $fieldname
     * @param       mixed $default
     * 
     * @return      mixed The field's value or $default if the field is not set.
     */
    public function getValue($fieldname, $default = NULL);

    /**
     * Return an array holding the names of all field's that we provide values for.
     * 
     * @return       array
     */
    public function getSupportedFields();
    
    /**
     * Return an array representation of this record.
     * 
     * @return array
     */
    public function toArray();
    
    /**
     * Return an unique string that identifies this record.
     * 
     * @return string
     */
    public function getIdentifier();
}

?>