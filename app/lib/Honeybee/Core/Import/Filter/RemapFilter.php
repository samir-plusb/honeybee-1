<?php

namespace Honeybee\Core\Import\Filter;

class RemapFilter extends BaseFilter
{
    protected function run(array $input)
    {
        $mappedData = array();
        $keyMap = $this->getConfig()->getSetting('map');
        $includeUnmapped = $this->getConfig()->getSetting('include_unmapped', FALSE);

        foreach ($input as $key => $value)
        {
            if (isset($keyMap[$key]))
            {
                $mappedData[$keyMap[$key]] = $value;
            }
            else if (TRUE === $includeUnmapped)
            {
                $mappedData[$key] = $value;
            }
        }

        return parent::run($mappedData);
    }
}
