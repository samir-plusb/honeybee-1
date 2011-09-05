<?php

class DataImportFactoryConfig extends XmlFileBasedConfig
{
    const CFG_CLASS = 'class';

    const CFG_NAME = 'name';

    const CFG_DESCRIPTION = 'description';

    const CFG_SETTINGS = 'settings';

    const CFG_DATASRC = 'datasource';

    /**
     * @return array<string>
     */
    public function getRequiredSettings()
    {
        return array_merge(
            parent::getRequiredSettings(),
            array(
                self::CFG_CLASS,
                self::CFG_NAME,
                self::CFG_DESCRIPTION,
                self::CFG_SETTINGS,
                self::CFG_DATASRC
            )
        );
    }
}

?>