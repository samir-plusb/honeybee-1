<?php

abstract class XmlFileBasedConfig extends ImportBaseConfig
{
    protected function loadConfig()
    {
        $uriParts = $this->getUriParts();
        $config = include AgaviConfigCache::checkConfig($uriParts['path']);
        
        if (empty($config['import']))
        {
            return array();
        }
        
        return $config['import'];
    }
}

?>
