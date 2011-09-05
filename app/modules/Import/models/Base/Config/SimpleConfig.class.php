<?php

abstract class SimpleConfig extends ImportBaseConfig
{
    protected function load($configSrc)
    {
        if (!is_array($configSrc))
        {
            throw new ImportConfigException("The given config source is expected to be by the type of 'array' but is not.");
        }

        return $configSrc;
    }
}

?>
