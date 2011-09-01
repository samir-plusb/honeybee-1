<?php

class ImperiaDataSourceConfig extends SimpleConfig
{
    const CFG_URL = 'url';
    
    const CFG_ACCOUNT_USER = 'account_user';
    
    const CFG_ACCOUNT_PASS = 'account_pass';
    
    const CFG_RECORD_TYPE = 'record';
    
    const CFG_DOCUMENT_IDS = 'doc_ids';
    
    protected function getRequiredSettings()
    {
        return array(
            self::CFG_URL,
            self::CFG_RECORD_TYPE,
            self::CFG_ACCOUNT_USER,
            self::CFG_ACCOUNT_PASS,
            self::CFG_DOCUMENT_IDS
        );
    }
}

?>
