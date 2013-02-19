<?php

class TreeConfigValidator extends AgaviValidator
{
    protected function validate()
    {
        $config = $this->getData($this->getArgument());

        if (is_array($config))
        {
            $config = new TreeConfig($config);
        }
        elseif (! $config instanceof ITreeConfig)
        {
            $this->throwError('type');
            return FALSE;
        }

        if ($this->validateTreeConfigInstance($config))
        {
            $this->export($this->getArgument(), $config);
            return TRUE;
        }
        return FALSE;
    }

    protected function validateTreeConfigInstance(ITreeConfig $treeConfig)
    {
        // @todo validate all important parameters
        return TRUE;
    }
}

?>
