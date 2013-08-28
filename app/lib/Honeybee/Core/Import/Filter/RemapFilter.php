<?php

namespace Honeybee\Core\Import\Filter;

class RemapFilter extends BaseFilter
{
    protected function run(array $input)
    {
        $mappedOutput = array();

        $propertyMap = $this->getConfig()->get('map', array());
        $includeUnmapped = $this->getConfig()->get('include_unmapped', FALSE);

        foreach ($propertyMap as $inputKey => $outputMapping)
        {
            $inputData = null;
            $outputKey = $outputMapping;

            if (is_array($outputMapping) && isset($outputMapping['static_value']))
            {
                $inputData = $outputMapping['static_value'];
                $outputKey = $inputKey;
                if (!$this->getConfig()->get('static_values_in_empty_arrays', FALSE))
                {
                    $arrayKey = preg_replace('~\[\w+\]$~is', '', $outputKey);
                    if ($arrayKey !== $outputKey)
                    {
                        $parentArray = array_filter(self::getArrayValue($mappedOutput, $arrayKey));
                        if (empty($parentArray))
                        {
                            continue;
                        }
                    }
                }
            }
            else if (!is_array($outputMapping))
            {
                $inputData = self::getArrayValue($input, $inputKey);
            }
            else
            {
                throw new \Exception("Invalid (re)mapping configuration given to " . __METHOD__);
            }

            self::setArrayValue($mappedOutput, $outputKey, $inputData);
        }

        if (TRUE === $includeUnmapped)
        {
            foreach (array_diff(array_keys($input), array_keys($propertyMap)) as $unmappedKey)
            {
                $mappedOutput[$unmappedKey] = $input[$unmappedKey];
            }
        }

        return parent::run($mappedOutput);
    }

    public static function getArrayValue(array &$array, $path)
    {
        $parsedPath = self::getPartsFromPath($path);
        $curPart = $parsedPath['parts'][0];
        $value = &$array[$curPart];
        $pathPartCount = count($parsedPath['parts']);

        for ($i = 1; $i < $pathPartCount; $i++)
        {
            $curPart = $parsedPath['parts'][$i];
            $value = &$value[$curPart];
        }

        return $value;
    }

    public static function setArrayValue(array &$array, $path, $value)
    {
        $parsedPath = self::getPartsFromPath($path);
        $curPart = $parsedPath['parts'][0];

        if (! isset($array[$curPart]))
        {
            $array[$curPart] = array();
        }
        $valuePath = &$array[$curPart];
        $pathPartCount = count($parsedPath['parts']);

        for ($i = 1; $i < $pathPartCount; $i++)
        {
            $curPart = $parsedPath['parts'][$i];
            if (! isset($valuePath[$curPart]))
            {
                $valuePath[$curPart] = array();
            }
            $valuePath = &$valuePath[$curPart];
        }

        $valuePath = $value;
    }

    public static function getPartsFromPath($path)
    {
        if (strlen($path) == 0)
        {
            return array('parts' => array(), 'absolute' => TRUE);
        }

        $parts = array();
        $absolute = ($path[0] != '[');

        if (($pos = strpos($path, '[')) === FALSE)
        {
            if (strpos($path, ']') !== FALSE)
            {
                throw new \InvalidArgumentException('Invalid "]" without opening "[" found');
            }

            $parts[] = $path;
        }
        else
        {
            $state = 0;
            $cur = '';

            foreach (str_split($path) as $c)
            {
                // this is the fastest way to loop over an string
                switch ($state)
                {
                    // the order is significant for performance
                    case 2:
                    {
                        // match all characters between []
                        if ($c == ']')
                        {
                            $parts[] = $cur;
                            $cur = '';
                            $state = 1;
                        }
                        else if ($c == '[')
                        {
                            throw new \InvalidArgumentException('Invalid "[[" found');
                        }
                        else
                        {
                            $cur .= $c;
                        }
                        break;
                    }
                    case 0:
                    {
                        // match everything to the first '['
                        if ($c != '[')
                        {
                            $cur .= $c;
                        }
                        else
                        {
                            if ($cur !== '')
                            {
                                $parts[] = $cur;
                                $cur = '';
                            }
                            $state = 2;
                        }
                        break;
                    }
                    case 1:
                    {
                        // match exactly '['
                        if ($c == '[')
                        {
                            $state = 2;
                        }
                        else
                        {
                            throw new \InvalidArgumentException('Invalid character after "]" found');
                        }
                        break;
                    }
                }
            }

            if ($state == 0)
            {
                $parts[] = $cur;
            }
            else if ($state == 2)
            {
                throw new \InvalidArgumentException('Missing "]" after opening "["');
            }
        }

        return array('parts' => $parts, 'absolute' => $absolute);
    }
}
