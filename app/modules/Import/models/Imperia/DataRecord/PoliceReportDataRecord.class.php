<?php

/**
 * The PoliceReportDataRecord class is a concrete implementation of the ImperiaDataRecord base class.
 * It reflects a single dataset coming from the 'police-reports' content provider.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
class PoliceReportDataRecord extends ImperiaDataRecord
{
    const SOURCE_NAME = 'polizeimeldungen';
    
    protected function getSourceName()
    {
        return self::SOURCE_NAME;
    }
}

?>