<?php

/**
 * Settings for newswire datasource
 *
 * @version         $Id$
 * @author          Tom Anheyer
 * @package         Import
 * @subpackage      Newswire
 */
class NewswireDataSourceConfig extends DataSourceConfig
{
    const CFG_GLOB = 'glob';
    
    const CFG_TIMESTAMP_FILE = 'timestamp_file';

    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_GLOB,
                self::CFG_TIMESTAMP_FILE
            )
        );
    }
}

?>