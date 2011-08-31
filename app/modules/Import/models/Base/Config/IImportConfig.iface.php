<?php

interface IImportConfig
{
    /**
     * @param string $configUri
     */
    public function __construct($configUri);
    
    /**
     * @param string $setting
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getSetting($setting, $default = null);
    
    /**
     * @return array<string>
     */
    public function getSupportSettings();
    
    /**
     * @return array
     */
    public function getUriParts();
    
    /**
     * @return string
     */
    public function getUri();
}

?>
