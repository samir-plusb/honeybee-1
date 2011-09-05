<?php

abstract class ImportBaseConfig implements IImportConfig
{
    private $settings;

    abstract protected function load($configSrc);
    
    abstract protected function getRequiredSettings();
    
    public function __construct($configSrc)
    {
        $this->init($configSrc);
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
    
    protected function init($configSrc)
    {
        $settings = $this->load($configSrc);
        
        $this->validateConfig($settings);
        
        $this->settings = $settings;
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