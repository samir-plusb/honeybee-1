<?php

/**
 * The ShofiVerticalsJsonDataRecord class is a concrete implementation of the ShofiVerticalsDataRecord base class.
 * It provides handling for mapping data coming from the json-verticals-catalog into the local vertical record format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi_Verticals
 * @subpackage      Import/Json
 */
class ShofiVerticalsJsonDataRecord extends ShofiVerticalsDataRecord
{
    /**
     * Map the incoming json-catalog style (array)data into the local shofi-vertical format.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        return array(
            self::PROP_IDENT => $data['_id'],
            self::PROP_NAME => $data['name'],
            self::PROP_TEASER => $data['teaser']
        );
    }
}

?>
