<?php

class PlacesRefCountRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        if (empty($value))
        {
            return '';
        }
        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $listState = ListState::fromArray(array(
            'limit' => 5000,
            'offset' => 0,
            'filter' => array(
                'detailItem.category' => $value
            )
        ));
        return $finder->count($listState);
    }
}
