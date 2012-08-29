<?php

/**
 * The ScreeningXmlDataRecord class is a concrete implementation of the MoviesDataRecord base class.
 * It provides handling for mapping data coming from the screenings xml import into the local movie-record format.
 *
 * @version         $Id: ScreeningXmlDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import/Screening
 */
class ScreeningXmlDataRecord extends MoviesDataRecord
{
    /**
     * Override our parents toArray so we only update the screenings field
     * when importing this record type.
     */
    public function toArray()
    {
        return array(
            self::PROP_IDENT => $this->getIdentifier(),
            self::PROP_SCREENINGS => $this->getScreenings(),
            self::PROP_TIMESTAMP => $this->getTimestamp(),
            self::PROP_SOURCE => $this->getSource(),
            self::PROP_ORIGIN => $this->getOrigin()
        );
    }

    /**
     * Map the incoming movie data (array) to our masterRecord structure.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        return array(
            self::PROP_IDENT => $data['movieId'],
            self::PROP_SCREENINGS => $data['screenings']
        );
    }
}

?>
