<?php

interface IImportConfig
{
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
}

?>
