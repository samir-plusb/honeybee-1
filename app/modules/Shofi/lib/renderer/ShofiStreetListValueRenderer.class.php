<?php

class ShofiStreetListValueRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $displayVal = $value;
        $coreItem = $data['data']['coreItem'];
        $location = $coreItem['location'];
        if ($value && isset($location['housenumber']))
        {
            $displayVal = $value . ' ' . $location['housenumber'];
        }
        return $displayVal;
    }
}
