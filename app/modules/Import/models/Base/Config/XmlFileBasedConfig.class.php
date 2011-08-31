<?php

abstract class XmlFileBasedConfig extends ResourceBasedConfig
{
    protected function loadResource()
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
