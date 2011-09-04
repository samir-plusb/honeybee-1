<?php

abstract class DataSourceConfig extends SimpleConfig
{
    const CFG_RECORD_TYPE = 'record';
    
    public function getRequiredSettings()
    {
        return array(
            self::CFG_RECORD_TYPE
        );
    }
}

?>
