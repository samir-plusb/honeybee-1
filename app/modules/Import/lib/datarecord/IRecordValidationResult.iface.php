<?php

/**
 * The RecordValidationResult class represents the result a the IDataRecord's validation routine.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataRecord
 */
interface IRecordValidationResult
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds a value that that reflects successfull validation.
     */
    const STATE_OK = 'ok';

    /**
     * Holds a value which indicates that validation errors have occured.
     */
    const STATE_ERR = 'error';

    // ---------------------------------- </CONSTANTS> -------------------------------------------

    /**
     * Return our validation state.
     * Either self::STATE_OK or self::STATE_ERR.
     *
     * @return      string
     */
    public function getState();

    /**
     * Return an array containing our validation errors.
     *
     * @return      array
     */
    public function getErrors();

    /**
     * Returns true if their is an error for the given name.
     * If the $name parameter is omitted, the mere existance of errors is checked.
     *
     * @param       string $name
     *
     * @return      boolean
     */
    public function hasError($name = NULL);

    /**
     * Adds a given error to the result.
     *
     * @param       string $name
     * @param       string $message
     */
    public function addError($name, $message);

    /**
     * Return the error for the given name.
     * Returns NULL if the there is no such error.
     *
     * @param       string $name
     *
     * @return      string
     */
    public function getError($name);
}

?>