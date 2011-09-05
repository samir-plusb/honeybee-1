<?php

class ImperiaDataSourceConfig extends DataSourceConfig
{
    const PARAM_DOCIDS = 'doc_ids';
    
    const CFG_URL = 'url';
    
    const CFG_ACCOUNT_USER = 'account_user';
    
    const CFG_ACCOUNT_PASS = 'account_pass';
    
    const CFG_DOC_IDLIST_URL = 'doc_idlist_url';
    
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_URL,
                self::CFG_ACCOUNT_USER,
                self::CFG_ACCOUNT_PASS,
                self::CFG_DOC_IDLIST_URL
            )
        );
    }
}

?>
