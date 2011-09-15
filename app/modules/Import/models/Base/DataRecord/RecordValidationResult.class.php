<?php

/**
 * The RecordValidationResult class represents the result a the IDataRecord's validation routine.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
class RecordValidationResult implements IRecordValidationResult
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds a value that that reflects successfull validation.
     */
    const STATE_OK = TRUE;
    
    /**
     * Holds a value which indicates that validation errors have occured.
     */
    const STATE_ERR = FALSE;
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds one of our STATE_* constant and reflects our current validation state.
     * 
     * @var         boolean
     */
    protected $state;
    
    /**
     * An array holding all validation errors that occured during validation.
     * The array structure holds the error names as key and the corresponding error messages as value
     * as shown in the follwoing example:
     * ->    array(
     *           'identifier' => 'THe record's identifier is invalid.'
     *       )
     * 
     * @var         array
     */
    protected $errors;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRCUTOR> ------------------------------------------
    
    /**
     * Create new RecordValidationResult instance thereby initializing our members.
     */
    public function __construct()
    {
        $this->state = self::STATE_OK;
        $this->errors = array();
    }
    
    // ---------------------------------- </CONSTRCUTOR> -----------------------------------------
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Return our validation state.
     * Either self::STATE_OK or self::STATE_ERR.
     * 
     * @return      boolean
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Return an array containing our validation errors.
     * 
     * @return      array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Returns true if their is an error for the given name.
     * If the $name parameter is omitted, the mere existance of errors is checked.
     * 
     * @param       string $name
     * 
     * @return      boolean
     */
    public function hasError($name = NULL)
    {
        if (NULL === $name)
        {
            return (0 < count($this->errors));
        }
        
        return isset($this->errors[$name]);
    }
    
    /**
     * Adds a given error to the result.
     * 
     * @param       string $name
     * @param       string $message 
     */
    public function addError($name, $message)
    {
        if (self::STATE_ERR !== $this->state)
        {
            $this->state = self::STATE_ERR;
        }
        
        $this->errors[$name] = $message;
    }
    
    /**
     * Return the error for the given name.
     * Returns NULL if the there is no such error.
     * 
     * @param       string $name
     * 
     * @return      string
     */
    public function getError($name)
    {
        return $this->hasError($name)
            ? $this->errors[$name] 
            : NULL;
    }
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}

?>