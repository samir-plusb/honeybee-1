<?php

/**
 * The IImportItem interface defines the requirements towards class implementations that would like to provide
 * location information about (content) items.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IItemLocation
{
    /**
     * Returns an array holding the location's longitude and latitude.
     *
     * <pre>
     * Example value structure:
     * array(
     *     'lon' => 12.345,
     *     'lat'  => 23.456
     * )
     * </pre>
     *
     * @return array
     */
    public function getCoordinates();

    /**
     * Returns the location's city (berlin ...).
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns the location's postal code.
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Returns the locations administrative district (pankow, mitte ...).
     *
     * @return string
     */
    public function getAdministrativeDistrict();

    /**
     * Returns the locations district (prenzlauer berg, wedding ...)
     *
     * @return string
     */
    public function getDistrict();

    /**
     * Returns the location's neighborhood (sprengel kiez, niederschönhausen).
     *
     * @return string
     */
    public function getNeighborHood();

    /**
     * Returns the location's street.
     *
     * @return string
     */
    public function getStreet();

    /**
     * Returns the location's housenumber.
     *
     * @return string
     */
    public function getHouseNumber();

    /**
     * Returns the location's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the location's relevance
     *
     * @return int
     */
    public function getRelevance();

    /**
     * Returns an array representation of the location.
     *
     * <pre>
     * Example structure:
     * array(
     *     'coords'                  => array(
     *         'lon' => '12.19281',
     *         'lat' => '13.2716'
     *     ),
     *     'city'                    => 'Berlin',
     *     'postal_code'             => '13187',
     *     'administrative_district' => 'Pankow',
     *     'district'                => 'Prenzlauer Berg',
     *     'neighborhood'            => 'Niederschönhausen',
     *     'street'                  => 'Shrinkstreet',
     *     'house_num'               => '23',
     *     'name'                    => 'Vereinsheim Pankow - Niederschönhausen',
     *     'relevance'               => 1
     * )
     * </pre>
     *
     * @return string
     */
    public function toArray();
}

?>
