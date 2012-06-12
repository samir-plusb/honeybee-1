<?php

/**
 * IDataRecord implementations are responseable for normalizing and then transporting
 * data that represents a single data record.
 *
 * !!! All dates formats handled and provided by data records shall be in the 'ISO 8601' date format. !!!
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataRecord
 */
interface IDataRecord
{
    /**
     * Return an unique string that identifies this record.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Return this IDataRecord's source.
     * Will usually be a name or term related to the datasource
     * that created this record instance.
     *
     * @return      string
     */
    public function getSource();

    /**
     * Return this IDataRecord's timestamp.
     *
     * can be record last change time, message issue date, mail date, ...
     *
     * @return      string
     */
    public function getTimestamp();

    /**
     * Return this IDataRecord's data origin (url, filepath eg).
     *
     * @return      string
     */
    public function getOrigin();

    /**
     * Return an array representation of this record.
     *
     * @return array
     */
    public function toArray();

    /**
     * Validates that the given record is in a consistent state
     * and is ready to be thrown into the domain.
     *
     * @return      IRecordValidationResult
     *
     * @todo Instead of returning an array we should return a ValidationResult object.
     */
    public function validate();
}

?>