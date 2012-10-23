<?php

class ShofiDetailItemValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());
        $this->export($data, $this->getArgument());
        return true;
    }
}

?>