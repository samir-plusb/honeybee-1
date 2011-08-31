<?php

class ImperiaImportFactoryConfig extends XmlFileBasedConfig
{
    const CFG_CLASS = 'class';
    
    const CFG_NAME = 'name';
    
    const CFG_DESCRIPTION = 'description';
    
    const CFG_SETTINGS = 'settings';
    
    const CFG_DATASRC = 'datasource';
    
    /**
     * @return array<string>
     */
    protected function getRequiredSettings()
    {
        return array(
            self::CFG_CLASS,
            self::CFG_NAME,
            self::CFG_DESCRIPTION,
            self::CFG_SETTINGS,
            self::CFG_DATASRC
        );
    }
}

?>
