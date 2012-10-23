<?php

class MovieScreeningsProvider
{
    protected static $versionMappings = array(
        1 => 'OV', 2 => 'OmU', 3 => 'OmenglU',
        6 => 'OmfrzU', 7 => 'OmfrzunlU', 8 => 'DFmenglU',
        9 => 'DFmfrzunlU', 20 => 'VHS-Kino', 21 => 'VHS-Kino OV'
    );

    protected $screeningsMap = array();

    public function load($xmlFilepath)
    {
        $parser = new ScreeningXmlParser();
        $this->screeningsMap = array();

        foreach ($parser->parseXml($xmlFilepath) as $row)
        {
            $movieVersions = array();
            foreach ($row['screenings'] as &$screening)
            {
                if (($version = $screening['version']) && isset(self::$versionMappings[$version]))
                {
                    $mappedVersion = self::$versionMappings[$version];
                    if (! in_array($mappedVersion, $movieVersions))
                    {
                        $movieVersions[] = $mappedVersion;
                    }
                    $screening['version'] = $mappedVersion;
                }
                else
                {
                    $screening['version'] = NULL;
                }
            }
            $this->screeningsMap[$row['movieId']] = $row['screenings'];
        }
    }

    public function hasScreeningsForMovie($movieId)
    {
        return isset($this->screeningsMap[$movieId]);
    }

    public function getScreeningsForMovie($movieId)
    {
        return $this->hasScreeningsForMovie($movieId)
            ? $this->screeningsMap[$movieId] : NULL;
    }
}
