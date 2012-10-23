<?php

class MoviesAssetRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        return ! empty($value) ? 'Ja' : 'Nein';
    }
}
