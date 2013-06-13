<?php

namespace Honeybee\Core\Import\Filter;

/**
 * Useful for importing text-collections as strings can be represented as
 * arrays.
 */
class StringToArrayRemapFilter extends BaseFilter
{
    protected function run(array $input)
    {
        $output = $input;

        $map = $this->getConfig()->get('map', array());
        $separator = $this->getConfig()->get('separator', '|');

        foreach ($map as $input_key => $output_key)
        {
            $value = explode($separator, $input[$input_key]);

            if (!is_array($value))
            {
                $value = array();
            }

            $output[$output_key] = $value;
        }

        return parent::run($output);
    }
}
