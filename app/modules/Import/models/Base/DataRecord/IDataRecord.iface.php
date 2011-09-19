<?php

/**
 * IDataRecord implementations are responseable for normalizing and then transporting
 * data that represents a single data record.
 *
 * !!! All dates formats handled and provided by data records shall be in the 'ISO 8601' date format. !!!
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
     * Returns our title.
     *
     * @return      string
     */
    public function getTitle();

    /**
     * Returns our content.
     *
     * @return      string
     */
    public function getContent();

    /**
     * Returns our category.
     *
     * @return      string
     */
    public function getCategory();

    /**
     * Returns our media (image, video and file assets for example).
     * The returned value is an array holding id's that can be used together with our ProjectAssetService
     * implementations.
     * Example return value structure:
     * -> return array(23, 24, 512, 13);
     *
     * @return      array
     */
    public function getMedia();

    /**
     * Returns our geo data in the following structure:
     * -> return array(
     *        'long' => $longValue,
     *        'lat'  => $latValue
     *    );
     *
     * @return      array
     */
    public function getGeoData();

    /**
     * Return an array representation of this record.
     *
     * @return array
     */
    public function toArray();

    /**
     * Validates that the given record is in a consistent state
     * and is ready to be thrown into the domain.
     * Returns an array containing the validation result.
     * -> array(
     *        'ok'    => FALSE,
     *        'errors => array(
     *            'title' => 'Invalid title given.',
     *            'id'    => 'The id is missing.'
     *        )
     *    );
     * -> array('ok' => TRUE);
     *
     * @return      array
     *
     * @todo Instead of returning an array we should return a ValidationResult object.
     */
    public function validate();
}

?>