<?php

/**
 * The FilmZeitReviewsDataRecord class is a concrete implementation of the MoviesDataRecord base class.
 * It handles data on movie reviews coming from our partner film-zeit.de.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Movies
 * @subpackage      Import/FilmZeitReviews
 */
class FilmZeitReviewsDataRecord extends MoviesDataRecord
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
            self::PROP_IDENT => sha1($data['title']),
            self::PROP_TITLE => $data['title'],
            self::PROP_TEASER => $data['teaser'],
            self::PROP_REVIEWS => $data['reviews']
        );
    }
}
