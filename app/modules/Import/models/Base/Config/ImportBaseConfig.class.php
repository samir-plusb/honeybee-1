<?php

abstract class ImportBaseConfig implements IImportConfig
{
    private $configUri;
    
    private $configUriParts;
    
    private $settings;

    abstract protected function loadConfig();
    
    abstract protected function getRequiredSettings();
    
    public function __construct($configUri)
    {
        
        $this->configUriParts = $this->parseUri($configUri);
        $this->configUri = $configUri;
        
        $this->init();
    }
    
    public function getSetting($setting, $default = null)
    {
        $value = $default;
        
        if (isset($this->settings[$setting]))
        {
            $value = $this->settings[$setting];
        }
        
        return $value;
    }
    
    public function getSupportSettings()
    {
        return array_keys($this->settings);
    }
    
    public function getUriParts()
    {
        return $this->configUriParts;
    }
    
    public function getUri()
    {
        return $this->configUri;
    }
    
    protected function init()
    {
        $settings = $this->loadConfig();
        
        $this->validateConfig($settings);
        
        $this->settings = $settings;
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
    
    protected function validateConfig(array $settings)
    {
        foreach ($this->getRequiredSettings() as $required_setting)
        {
            if (!isset($settings[$required_setting]))
            {
                throw new ImportConfigException("Missing setting '" . $required_setting . "' for config.");
            }
        }
    }
}

?>
