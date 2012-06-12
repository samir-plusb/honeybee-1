<?php

class ListConfigValidator extends AgaviValidator
{
    protected function validate()
    {
        $config = $this->getData($this->getArgument());

        if (is_array($config))
        {
            $config = ListConfig::fromArray($config);
        }
        elseif (! $config instanceof IListConfig)
        {
            $this->throwError('type');
            return FALSE;
        }

        if ($this->validateListConfigInstance($config))
        {
            $this->export($this->getArgument(), $config);
            return TRUE;
        }
        return FALSE;
    }

    protected function validateListConfigInstance(IListConfig $listConfig)
    {
        // @todo validate all important parameters
        return TRUE;
    }
}

?>
