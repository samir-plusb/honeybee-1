<?php

/**
 * The MoviesXmlDataRecord class is a concrete implementation of the MoviesDataRecord base class.
 * It provides handling for mapping data coming from the xml import into the local movie-record format.
 *
 * @version         $Id: MoviesXmlDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import/Xml
 */
class MoviesXmlDataRecord extends MoviesDataRecord
{
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
            self::PROP_IDENT => $data['id'],
            self::PROP_TITLE => $data['title'],
            self::PROP_TEASER => $data['teaser'],
            self::PROP_DIRECTOR => $data['director'],
            self::PROP_ACTORS => $data['actors'],
            self::PROP_RENTAL => $data['rental'],
            self::PROP_GENRE => $data['genre'],
            self::PROP_FSK => $data['fsk'],
            self::PROP_COUNTRY => isset($data['country']) ? $data['country'] : NULL,
            self::PROP_RELEASE_DATE => isset($data['release_date']) ? $data['release_date'] : NULL,
            self::PROP_DURATION => isset($data['duration']) ? $data['duration'] : NULL,
            self::PROP_YEAR => isset($data['year']) ? $data['year'] : NULL,
            self::PROP_MEDIA => isset($data['media']) ? $data['media'] : array(),
            self::PROP_SOURCE => 'movies-xml-import'
        );
    }
}

?>
