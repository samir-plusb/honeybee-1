<?php

class MoviesXmlDataSourceConfig extends DataSourceConfig
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our the filepath setting that is used to configure our source file.
     */
    const CFG_FILE_PATH = 'file_path';

    /**
     * Holds the name of the screening's filepath settings that is used to provide movie screenings.
     */
    const CFG_SCREENINGS_FILE_PATH = 'screenings_file_path';

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
                self::CFG_FILE_PATH,
                self::CFG_SCREENINGS_FILE_PATH
            )
        );
    }

    // ---------------------------------- <DataSourceConfig OVERRIDES> ---------------------------
}
