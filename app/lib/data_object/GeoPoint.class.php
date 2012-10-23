<?php

class GeoPoint extends BaseDataObject
{
    const UNIT_MILES = 'miles';

    const UNIT_INCHES = 'inches';

    const UNIT_FEET = 'feet';

    const UNIT_NAUTICAL_MILES = 'nautical_miles';

    const UNIT_KILOMETERS = 'kilometers';

    const UNIT_METERS = 'meters';

    const EARTH_RADIUS = 3959;

    /**
     * Map holding factors to convert distance values from miles to X and the other way around.
     */
    protected static $unitMap = array(
        self::UNIT_KILOMETERS => 1.609344,
        self::UNIT_METERS => 1609.344,
        self::UNIT_NAUTICAL_MILES => 0.868976242,
        self::UNIT_FEET => 5280,
        self::UNIT_INCHES => 63360,
        self::UNIT_MILES => 1
    );

    protected $lon = 0;

    protected $lat = 0;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getLon()
    {
        return $this->lon;
    }

    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Calculates the distance between to geographic coordinates based on:
     * http://en.wikipedia.org/wiki/Spherical_law_of_cosines
     * For more accuracy use:
     * http://en.wikipedia.org/wiki/Haversine_formula
     *
     * @param array $point 
     * 
     * @return 
     */
    public function calculateDistance(GeoPoint $point, $unit = self::UNIT_MILES)
    {
        if (! isset(self::$unitMap[$unit]))
        {
            throw new InvaliArgumentException(
                "Invalid unit type passed to getDistance method. Only the following units are acceptable:\n" 
                . implode(', ', array_keys(self::$unitMap))
            );
        }

        $radius = self::EARTH_RADIUS * self::$unitMap[$unit];
        $deltaRadLat = deg2rad($point->getLat() - $this->getLat());
        $deltaRadLon = deg2rad($point->getLon() - $this->getLon());
        $radLat1 = deg2rad($this->getLat());
        $radLat2 = deg2rad($point->getLat());
        $sqHalfChord = sin($deltaRadLat / 2) * sin($deltaRadLat / 2) + cos($radLat1) * cos($radLat2) * sin($deltaRadLon / 2) * sin($deltaRadLon / 2);
        $angDistRad = 2 * asin(sqrt($sqHalfChord));

        return $radius * $angDistRad;
    }
}
