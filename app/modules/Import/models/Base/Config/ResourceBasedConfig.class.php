<?php

abstract class ResourceBasedConfig extends ImportBaseConfig implements IUriContainer
{
    private $configUri;
    
    private $configUriParts;
    
    abstract protected function loadResource();
    
    public function getUriParts()
    {
        return $this->configUriParts;
    }
    
    public function getUri()
    {
        return $this->configUri;
    }
    
    protected function load($configSrc)
    {
        if (!is_string($configSrc))
        {
            throw new ImportConfigException(("The given config-uri is expected to be by the type of 'string' but is not."));
        }
        
        $this->configUriParts = $this->parseUri($configSrc);
        $this->configUri = $configSrc;
        
        return $this->loadResource();
    }
    
    protected function parseUri($configUri)
    {
        $uriParts = parse_url($configUri);
        
        if (!$uriParts)
        {
            throw new ImportConfigException("Unable to parse the given uri: " . $configUri);
        }
        
        return $uriParts;
    }
}

?>
