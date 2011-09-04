<?php

class ImperiaDataSourceConfig extends DataSourceConfig
{
    const CFG_URL = 'url';
    
    const CFG_ACCOUNT_USER = 'account_user';
    
    const CFG_ACCOUNT_PASS = 'account_pass';
    
    const CFG_DOCUMENT_IDS = 'doc_ids';
    
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_URL,
                self::CFG_ACCOUNT_USER,
                self::CFG_ACCOUNT_PASS,
                self::CFG_DOCUMENT_IDS
            )
        );
    }
}

?>
