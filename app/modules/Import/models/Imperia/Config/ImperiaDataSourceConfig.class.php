<?php

class ImperiaDataSourceConfig extends SimpleConfig
{
    const URL = 'url';
    
    const ACCOUNT_USER = 'account_user';
    
    const ACCOUNT_PASS = 'account_pass';
    
    protected function getRequiredSettings()
    {
        return array(
            self::URL,
            self::ACCOUNT_USER,
            self::ACCOUNT_PASS
        );
    }
}

?>
