<?php

class ScreeningXmlDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our the filepath setting that is used to configure our source file.
     */
    const CFG_FILE_PATH = 'file_path';

    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------

    /**
     * Return an array of settings names,
     * that must be provided by our config source.
     *
     * @return      array
     *
     * @see         DataSourceConfig::getRequiredSettings()
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_FILE_PATH
            )
        );
    }

    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
}

?>
