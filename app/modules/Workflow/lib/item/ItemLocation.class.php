<?php

/**
 * The ItemLocation is a simple DTO style implementation of the IItemLocation interface.
 * It is responseable for providing location data for the content-items,
 * that are generated throughout the content refinement workflow.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
class ItemLocation implements IItemLocation
{
    /**
     * Holds the location's coordinates.
     *
     * @var array
     */
    protected $coordinates;

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
     * Holds the location's relevance.
     *
     * @var int
     */
    protected $relevance;

    /**
     * Creates a new ContentItem instance.
     */
    public function __construct(array $data = array())
    {
        $this->hydrate($data);
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
     * Returns the location's city (berlin ...).
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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

    /**
     * Returns the locations administrative district (pankow, mitte ...).
     *
     * @return string
     */
    public function getAdministrativeDistrict()
    {
        return $this->administrativeDistrict;
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

    /**
     * Returns the location's neighborhood (sprengel kiez, niederschönhausen).
     *
     * @return string
     */
    public function getNeighborHood()
    {
        return $this->neighborhood;
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

    /**
     * Returns the location's housenumber.
     *
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->housenumber;
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

    /**
     * Returns the location's relevance
     *
     * @return int
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * Returns an array representation of the location.
     *
     * @return string
     */
    public function toArray()
    {
        $props = array(
            'coordinates', 'city', 'postalCode',
            'administrativeDistrict', 'district', 'neighborhood',
            'street', 'housenumber', 'name', 'relevance'
        );
        $data = array();
        foreach ($props as $prop)
        {
            $getter = 'get' . ucfirst($prop);
            $data[$prop] = $this->$getter();
        }
        return $data;
    }

    /**
     * Hydrates the given data into the item.
     * This method is used to internally setup our state
     * and has privleged write access to all properties.
     * Properties that are set during hydrate dont mark the item as modified.
     *
     * @param array $data
     */
    protected function hydrate(array $data)
    {
        $simpleProps = array(
            'coordinates', 'city', 'postalCode',
            'administrativeDistrict', 'district', 'neighborhood',
            'street', 'housenumber', 'name', 'relevance'
        );
        foreach ($simpleProps as $prop)
        {
            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
                else
                {
                    $this->$prop = $data[$prop];
                }
            }
        }
    }
}

?>
