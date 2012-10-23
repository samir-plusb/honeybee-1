<?php

/**
 * The ItemLocation is a simple data object implementation of the IItemLocation interface.
 * It reflects a unique location and it's structure is optimized to represent especially german locations.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage DataObject
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ItemLocation extends BaseDataObject implements IItemLocation
{
    /**
     * Holds the location's coordinates.
     *
     * @var array
     */
    protected $coordinates = array();

    /**
     * Holds the location's city name.
     *
     * @var string
     */
    protected $city;

    /**
     * Holds the location's postal code.
     *
     * @var string
     */
    protected $postalCode;

    /**
     * Holds the location's administrative district.
     *
     * @var string
     */
    protected $administrativeDistrict;

    /**
     * Holds the location's district name.
     *
     * @var string
     */
    protected $district;

    /**
     * Holds the location's neighborhood name.
     *
     * @var string
     */
    protected $neighborhood;

    /**
     * Holds the location's street name.
     *
     * @var string
     */
    protected $street;

    /**
     * Holds the location's housenumber.
     *
     * @var string
     */
    protected $housenumber;

    /**
     * Holds the location's name.
     *
     * @var string
     */
    protected $name;

    /**
     * Holds further details on the location.
     *
     * @var string
     */
    protected $details;

    /**
     * Holds the location's relevance.
     *
     * @var int
     */
    protected $relevance;

    /**
     * Create a new ItemLocation from the given data.
     *
     * Example value structure for the $data argument,
     * which is the same structure as the toArray method's return.
     *
     * <pre>
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
     *     'housenumber'             => '23',
     *     'name'                    => 'Vereinsheim Pankow - Niederschönhausen',
     *     'details'                 => 'great place to live',
     *     'relevance'               => 1
     * )
     * </pre>
     * @param array $data
     *
     * @return ItemLocation
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns an array holding the location's longitude and latitude.
     *
     * <pre>
     * Example value structure:
     * array(
     *     'long' => 12.345,
     *     'lat'  => 23.456
     * )
     * </pre>
     *
     * @return array
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Sets our coordinates.
     *
     * An example of the structure expected for the $coordinates argument:
     * <pre>
     * Example value structure:
     * array(
     *     'long' => 12.345,
     *     'lat'  => 23.456
     * )
     * </pre>
     *
     * @param array $coordinates An array containing long and lat position info.
     */
    public function setCoordinates($coordinates)
    {
        if (is_array($coordinates) && array_key_exists('lat', $coordinates)  && array_key_exists('lon', $coordinates))
        {
            $this->coordinates = array(
                'lat' => (float)$coordinates['lat'],
                'lon' => (float)$coordinates['lon']
            );
        }
    }

    /**
     * Returns the location's city (berlin ...).
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the location's postal code.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Returns the locations administrative district (pankow, mitte ...).
     *
     * @return string
     */
    public function getAdministrativeDistrict()
    {
        return $this->administrativeDistrict;
    }

    public function setAdministrativeDistrict($administrativeDistrict)
    {
        $this->administrativeDistrict = $administrativeDistrict;
    }

    /**
     * Returns the locations district (prenzlauer berg, wedding ...)
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * Returns the location's neighborhood (sprengel kiez, niederschönhausen).
     *
     * @return string
     */
    public function getNeighborHood()
    {
        return $this->neighborhood;
    }

    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;
    }

    /**
     * Returns the location's street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * Returns the location's housenumber.
     *
     * @return string
     */
    public function getHousenumber()
    {
        return $this->housenumber;
    }

    public function setHousenumber($housenumber)
    {
        $this->housenumber = $housenumber;
    }

    /**
     * Returns the location's name. (Vereinsheim Pankow ...)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the location's details.
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Returns the location's relevance
     *
     * @return int
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    public function setRelevance($relevance)
    {
        $this->relevance = $relevance;
    }

    public function asGeoPoint()
    {
        if (! empty($this->coordinates['lat']) && ! empty($this->coordinates['lon']))
        {
            return GeoPoint::fromArray($this->coordinates);
        }
        return NULL;
    }

    public static function parseStreet($street)
    {
        static $offsetStreet = 1;
        static $offsetNumber = 2;
        static $offsetDetail = 3;

        $number = NULL;
        $detail = NULL;

        if (! empty($street))
        {
            $pattern = '~([\wßüäö\s-\.]+)\s+([\d\w-|/]+)+(?:,\s*(.*))?~is';
            $matches = array();

            preg_match_all($pattern, $street, $matches);

            $matchCount = count($matches);
            if (3 <= $matchCount)
            {
                $street = isset($matches[$offsetStreet][0]) ? $matches[$offsetStreet][0] : $street;
                $number = isset($matches[$offsetNumber][0]) ? $matches[$offsetNumber][0] : NULL;
                if (isset($matches[$offsetDetail]))
                {
                    $detail = isset($matches[$offsetDetail][0]) ? $matches[$offsetDetail][0] : NULL;
                }
            }
        }

        return array('street' => $street, 'number' => $number, 'detail' => $detail);
    }
}
