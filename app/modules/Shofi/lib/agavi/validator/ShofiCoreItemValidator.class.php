<?php

class ShofiCoreItemValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());
        $this->export($data, 'coreItem');
        return true;
    }
}

?>