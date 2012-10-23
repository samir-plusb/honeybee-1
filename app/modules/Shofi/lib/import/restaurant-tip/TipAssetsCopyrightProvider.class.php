<?php

class TipAssetsCopyrightProvider
{
    protected $copyrightHolders = array();

    public function __construct($filePath)
    {
        if (! is_readable($filePath))
        {
            throw new InvalidArgumentException(
                "Invalid/unreadable file path was passed to the TipAssetsCopyrightProvider."
            );
        }

        foreach (file($filePath) as $line)
        {
            $parts = explode(':', trim($line));
            if (2 === count($parts))
            {
                list($acronym, $name) = array_map(function($part)
                {
                    return trim($part);
                }, $parts);
                $this->copyrightHolders[$acronym] = $name;
            }
        }
    }

    public function resolveAcronym($acronym)
    {
        if (isset($this->copyrightHolders[$acronym]))
        {
            return $this->copyrightHolders[$acronym];
        }

        return $acronym;
    }
}
